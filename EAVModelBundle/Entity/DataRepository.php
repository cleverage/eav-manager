<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\EAVModelBundle\Entity;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Entity\DataRepository as BaseDataRepository;

/**
 * Additional methods to data repositories.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DataRepository extends BaseDataRepository
{
    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function getQbLastByUser(User $user)
    {
        $qb = $this->createOptimizedQueryBuilder('d')
            ->where('d.createdBy = :user1 OR d.updatedBy = :user2')
            ->orderBy('d.updatedAt', 'desc')
            ->setParameters(
                [
                    'user1' => $user,
                    'user2' => $user,
                ]
            );

        return $qb;
    }
}
