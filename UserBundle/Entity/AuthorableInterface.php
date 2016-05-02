<?php

namespace CleverAge\EAVManager\UserBundle\Entity;


interface AuthorableInterface
{
    /**
     * @return User
     */
    public function getUpdatedBy();

    /**
     * @param User $user
     * @return mixed
     */
    public function setUpdatedBy(User $user);

    /**
     * @return User
     */
    public function getCreatedBy();

    /**
     * @param User $user
     * @return mixed
     */
    public function setCreatedBy(User $user);
}
