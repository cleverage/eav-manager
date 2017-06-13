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

    /**
     * @param string            $alias
     * @param null              $indexBy
     * @param QueryBuilder|null $qb
     *
     * @return QueryBuilder
     */
    public function createOptimizedQueryBuilder($alias, $indexBy = null, QueryBuilder $qb = null)
    {
        if (!$qb) {
            $qb = $this->createQueryBuilder($alias, $indexBy);
        }
        $qb->addSelect('values')
            ->leftJoin($alias.'.values', 'values');

        return $qb;
    }
}
