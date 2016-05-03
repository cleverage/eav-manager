<?php

namespace CleverAge\EAVManager\EAVModelBundle\Entity;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Entity\DataRepository as BaseDataRepository;

class DataRepository extends BaseDataRepository
{
    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function getQbLastByUser(User $user)
    {
        $qb = $this->createOptimizedQueryBuilder('d')
            ->where('d.createdBy = :user1 OR d.updatedBy = :user2')
            ->orderBy('d.updatedAt', 'desc')
            ->setParameters([
                'user1' => $user,
                'user2' => $user,
            ]);

        return $qb;
    }

    /**
     * @param                   $alias
     * @param null              $indexBy
     * @param QueryBuilder|null $qb
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
