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

use CleverAge\EAVManager\UserBundle\Entity\Group;

/**
 * Allows the access to a group based on the ROLE_USER_MANAGER role
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class GroupVoter extends UserVoter
{
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_a($class, Group::class, true);
    }
}
