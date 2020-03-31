<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Security;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Load the user for the firewall
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UserProvider implements UserProviderInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string */
    protected $userClass;

    /** @var bool */
    protected $allowEmailAsUsername;

    /**
     * @param ManagerRegistry $doctrine
     * @param string          $userClass
     * @param bool            $allowEmailAsUsername
     */
    public function __construct(ManagerRegistry $doctrine, $userClass = User::class, $allowEmailAsUsername = true)
    {
        $this->entityManager = $doctrine->getManagerForClass($userClass);
        $this->userClass = $userClass;
        $this->allowEmailAsUsername = $allowEmailAsUsername;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @throws UsernameNotFoundException
     *
     * @return User|UserInterface
     */
    public function loadUserByUsername($username)
    {
        /** @var User|UserInterface $user */
        $user = $this->getRepository()->findOneBy(['username' => $username]);
        if (!$user && $this->allowEmailAsUsername) {
            $user = $this->getRepository()->findOneBy(['email' => $username]);
        }
        if ($user) {
            return $user;
        }

        throw new UsernameNotFoundException($username);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @throws UsernameNotFoundException
     * @throws UnsupportedUserException  if the account is not supported
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException('Unsupported user');
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return is_a($class, $this->userClass, true);
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */

        return $this->entityManager->getRepository($this->userClass);
    }
}
