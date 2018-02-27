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
