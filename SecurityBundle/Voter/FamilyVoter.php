<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\SecurityBundle\Voter;

use Doctrine\Common\Collections\Collection;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use CleverAge\EAVManager\UserBundle\Entity\User;

/**
 * Allows the access to a family based on the family permissions of a user.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class FamilyVoter implements VoterInterface
{
    /** @var VoterInterface */
    protected $roleHierarchyVoter;

    /**
     * @param VoterInterface $roleHierarchyVoter
     */
    public function __construct(VoterInterface $roleHierarchyVoter)
    {
        $this->roleHierarchyVoter = $roleHierarchyVoter;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_a($class, FamilyInterface::class, true);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        if (!$object instanceof FamilyInterface) {
            return $result;
        }
        if (VoterInterface::ACCESS_GRANTED === $this->roleHierarchyVoter->vote($token, null, ['ROLE_DATA_ADMIN'])) {
            return VoterInterface::ACCESS_GRANTED;
        }
        $permissions = $this->extractPermissions($token);

        $result = VoterInterface::ACCESS_DENIED;
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

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
        return \in_array($attribute, FamilyPermission::getPermissions(), true);
    }

    /**
     * @param TokenInterface $token
     *
     * @return FamilyPermission[]|Collection
     *
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
