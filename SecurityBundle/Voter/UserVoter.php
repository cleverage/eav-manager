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

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use CleverAge\EAVManager\UserBundle\Entity\User;

/**
 * Allows the access to a user based on the ROLE_USER_MANAGER role
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UserVoter implements VoterInterface
{
    /** @var RoleHierarchyVoter */
    protected $roleHierarchyVoter;

    /**
     * @param RoleHierarchyVoter $roleHierarchyVoter
     */
    public function __construct(RoleHierarchyVoter $roleHierarchyVoter)
    {
        $this->roleHierarchyVoter = $roleHierarchyVoter;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_a($class, User::class, true);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        if (!$this->supportsClass($object)) {
            return $result;
        }
        if (VoterInterface::ACCESS_GRANTED === $this->roleHierarchyVoter->vote($token, null, ['ROLE_USER_MANAGER'])) {
            return VoterInterface::ACCESS_GRANTED;
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
        return \in_array(
            $attribute,
            [
                'list',
                'read',
                'create',
                'edit',
                'delete',
            ],
            true
        );
    }
}
