<?php

namespace CleverAge\EAVManager\SecurityBundle\Voter;



use Doctrine\Common\Collections\Collection;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use CleverAge\EAVManager\UserBundle\Entity\User;

class FamilyVoter implements VoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_a($class, 'Sidus\EAVModelBundle\Model\FamilyInterface', true);
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        if (!$object instanceof FamilyInterface) {
            return $result;
        }
        $permissions = $this->extractPermissions($token);

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = VoterInterface::ACCESS_DENIED;
            foreach ($permissions as $permission) {
                if ($permission->hasPermission($attribute) &&
                    $permission->getFamily()->getCode() === $object->getCode()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, FamilyPermission::getPermissions(), true);
    }

    /**
     * @param TokenInterface $token
     * @return FamilyPermission[]|Collection
     * @throws \UnexpectedValueException
     */
    protected function extractPermissions(TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return [];
        }
        return $user->getCombinedFamilyPermissions();
    }
}
