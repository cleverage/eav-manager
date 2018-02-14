<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Entity;

/**
 * Entities implementing this interface will be automatically "filled" with the current user info.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
interface AuthorableInterface
{
    /**
     * @return User
     */
    public function getUpdatedBy();

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function setUpdatedBy(User $user);

    /**
     * @return User
     */
    public function getCreatedBy();

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function setCreatedBy(User $user);
}
