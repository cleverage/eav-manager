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

namespace CleverAge\EAVManager\UserBundle\Infrastructure\Manager;

use CleverAge\EAVManager\UserBundle\Domain\Manager\UserManagerInterface;
use CleverAge\EAVManager\UserBundle\Entity\User;
use CleverAge\EAVManager\UserBundle\Exception\BadUsernameException;
use CleverAge\EAVManager\UserBundle\Mailer\UserMailer;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handles the creation, deletion and update of users
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UserManager implements UserManagerInterface
{
    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var UserPasswordEncoderInterface */
    protected $passwordEncoder;

    /** @var Registry */
    protected $doctrine;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var UserMailer */
    protected $userMailer;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param UserProviderInterface        $userProvider
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Registry                     $doctrine
     * @param ValidatorInterface           $validator
     * @param UserMailer                   $userMailer
     * @param LoggerInterface              $logger
     */
    public function __construct(
        UserProviderInterface $userProvider,
        UserPasswordEncoderInterface $passwordEncoder,
        Registry $doctrine,
        ValidatorInterface $validator,
        UserMailer $userMailer,
        LoggerInterface $logger
    ) {
        $this->userProvider = $userProvider;
        $this->passwordEncoder = $passwordEncoder;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->userMailer = $userMailer;
        $this->logger = $logger;
    }

    /**
     * @param string $username Username de l'utilisateur
     *
     * @throws BadUsernameException
     *
     * @return User
     */
    public function createUser($username)
    {
        $user = new User();
        $user->setUsername($username);
        $violations = $this->validator->validate($user);
        if ($violations->count() > 0) {
            throw BadUsernameException::createFromViolations($violations);
        }

        return $user;
    }

    /**
     * @param User   $user
     * @param string $plainTextPassword
     */
    public function setPlainTextPassword(User $user, $plainTextPassword)
    {
        $encoded = $this->passwordEncoder->encodePassword($user, $plainTextPassword);
        $user->setPassword($encoded);
        $user->setPasswordRequestedAt(null);
        $user->unsetAuthenticationToken();
        $user->setNew(false);
    }

    /**
     * @param User $user
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function requestNewPassword(User $user)
    {
        $user->setPasswordRequestedAt(new \DateTime());
        $user->resetAuthenticationToken();
        $user->setEmailSent(false);
        $this->save($user);
    }

    /**
     * @param User $user
     *
     * @throws \InvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function save(User $user)
    {
        if ($user->getPlainPassword()) {
            $this->setPlainTextPassword($user, $user->getPlainPassword());
        }

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();

        // Si l'utilisteur est nouveau
        if ($user->isNew() && !$user->isEmailSent()) {
            try {
                $this->userMailer->sendNewUserMail($user);
                $user->setEmailSent(true);
                $em->flush($user);
            } catch (\Exception $e) {
                $this->logger->alert($e->getMessage());
            }
        }
        // Si le mot de passe a expiré et que l'email n'a pas été envoyé
        if ($user->getPasswordRequestedAt() && !$user->isEmailSent()) {
            try {
                $this->userMailer->sendResetPasswordMail($user);
                $user->setEmailSent(true);
                $em->flush($user);
            } catch (\Exception $e) {
                $this->logger->alert($e->getMessage());
            }
        }
    }

    /**
     * @param User $user
     *
     * @throws \InvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function remove(User $user)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $em->remove($user);
        $em->flush($user);
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
     * @return User|UserInterface|null
     */
    public function loadUserByUsername($username)
    {
        return $this->userProvider->loadUserByUsername($username);
    }

    /**
     * Retrouve un utilisateur en se basant sur son token d'authentification
     * Utilisé uniquement pour le premier login et lors de la perte de mot de passe.
     *
     * @param string $token
     *
     * @return User|null
     */
    public function loadUserByToken($token)
    {
        return $this->getRepository()->findOneBy(
            [
                'authenticationToken' => $token,
            ]
        );
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
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->userProvider->refreshUser($user);
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
        return $this->userProvider->supportsClass($class);
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository()
    {
        return $this->doctrine->getRepository(User::class);
    }
}
