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

use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;

/**
 * Allows the access to a data based on it's family
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class EAVDataVoter implements VoterInterface
{
    /** @var FamilyVoter */
    protected $familyVoter;

    /**
     * @param FamilyVoter $familyVoter
     */
    public function __construct(FamilyVoter $familyVoter)
    {
        $this->familyVoter = $familyVoter;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_a($class, DataInterface::class, true);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof DataInterface) {
            return $this->familyVoter->vote($token, $object->getFamily(), $attributes);
        }

        return VoterInterface::ACCESS_ABSTAIN;
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
}
