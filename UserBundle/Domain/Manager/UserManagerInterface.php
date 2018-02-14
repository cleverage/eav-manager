<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Domain\Manager;

use CleverAge\EAVManager\UserBundle\Entity\User;
use CleverAge\EAVManager\UserBundle\Exception\BadUsernameException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Interface for user manager
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
interface UserManagerInterface extends UserProviderInterface
{
    /**
     * @param string $username Username de l'utilisateur
     *
     * @throws BadUsernameException
     *
     * @return User
     */
    public function createUser($username);

    /**
     * Change the user password
     *
     * @param User   $user
     * @param string $password
     */
    public function setPlainTextPassword(User $user, $password);

    /**
     * @param User $user
     *
     * @throws \InvalidArgumentException
     */
    public function requestNewPassword(User $user);

    /**
     * Save the user
     *
     * @param User $user
     */
    public function save(User $user);

    /**
     * Completely remove the user
     *
     * @param User $user
     *
     * @throws \InvalidArgumentException
     */
    public function remove(User $user);

    /**
     * Load a user with it's authentication token
     * Only used at first login and when retrieving a lost password
     *
     * @param string $token
     *
     * @return User|null
     */
    public function loadUserByToken($token);
}
