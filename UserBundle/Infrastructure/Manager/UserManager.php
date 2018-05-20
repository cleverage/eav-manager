<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Infrastructure\Manager;

use CleverAge\EAVManager\UserBundle\Domain\Manager\UserManagerInterface;
use CleverAge\EAVManager\UserBundle\Entity\User;
use CleverAge\EAVManager\UserBundle\Exception\BadUsernameException;
use CleverAge\EAVManager\UserBundle\Mailer\UserMailer;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

    /** @var ManagerRegistry */
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
     * @param ManagerRegistry              $doctrine
     * @param ValidatorInterface           $validator
     * @param UserMailer                   $userMailer
     * @param LoggerInterface              $logger
     */
    public function __construct(
        UserProviderInterface $userProvider,
        UserPasswordEncoderInterface $passwordEncoder,
        ManagerRegistry $doctrine,
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
     * @throws ORMException
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
     * @throws ORMException
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

        if ($user->isNew() && !$user->isEmailSent()) {
            try {
                $this->userMailer->sendNewUserMail($user);
                $user->setEmailSent(true);
                $em->flush($user);
            } catch (\Exception $e) {
                $this->logger->alert($e->getMessage());
            }
        }
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
     * @throws ORMException
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
