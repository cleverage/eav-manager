<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
