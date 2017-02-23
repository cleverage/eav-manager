<?php

namespace CleverAge\EAVManager\UserBundle\Domain\Manager;

use CleverAge\EAVManager\UserBundle\Entity\User;
use CleverAge\EAVManager\UserBundle\Exception\BadUsernameException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Interface pour la manipulation des utilisateurs
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
     * Modifie le mot de passe de l'utilisateur
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
     * Sauvegarde l'utilisateur
     *
     * @param User $user
     */
    public function save(User $user);

    /**
     * Supprime l'utilisateur complètement
     *
     * @param User $user
     *
     * @throws \InvalidArgumentException
     */
    public function remove(User $user);

    /**
     * Retrouve un utilisateur en se basant sur son token d'authentification
     * Utilisé uniquement pour le premier login et lors de la perte de mot de passe.
     *
     * @param string $token
     *
     * @return User|null
     */
    public function loadUserByToken($token);
}
