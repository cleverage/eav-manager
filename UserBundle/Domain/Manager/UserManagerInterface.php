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
