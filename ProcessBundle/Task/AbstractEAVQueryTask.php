<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sidus\EAVModelBundle\Doctrine\EAVFinder;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Handles EAV Data pagination
 */
abstract class AbstractEAVQueryTask extends AbstractEAVTask
{
    /** @var EAVFinder */
    protected $eavFinder;

    /**
     * AbstractEAVQueryTask constructor.
     *
     * @param EAVFinder $eavFinder
     */
    public function __construct(Registry $doctrine, FamilyRegistry $familyRegistry, EAVFinder $eavFinder)
    {
        parent::__construct($doctrine, $familyRegistry);
        $this->eavFinder = $eavFinder;
    }

    /**
     * {@inheritDoc}
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'criteria' => [],
                'extended_criteria' => [],
                'repository' => null,
                'order_by' => [],
                'limit' => null,
                'offset' => null,
            ]
        );
        $resolver->setNormalizer(
            'repository',
            function (Options $options, $value) {
                if ($value instanceof DataRepository) {
                    return $value;
                }
                /** @var FamilyInterface $family */
                $family = $options['family'];

                return $this->doctrine->getRepository($family->getDataClass());
            }
        );

        $resolver->setAllowedTypes('criteria', ['array']);
        $resolver->setAllowedTypes('extended_criteria', ['array']);
        $resolver->setAllowedTypes('repository', ['NULL', DataRepository::class]);
        $resolver->setAllowedTypes('order_by', ['array']);
        $resolver->setAllowedTypes('limit', ['NULL', 'integer']);
        $resolver->setAllowedTypes('offset', ['NULL', 'integer']);
    }

    /**
     * @deprecated Use getPaginator instead because this method can't handle limit and offset properly
     *
     * @param ProcessState $state
     * @param string       $alias
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder(ProcessState $state, $alias = 'e')
    {
        $options = $this->getOptions($state);

        $criteria = $options['extended_criteria'];
        foreach ($options['criteria'] as $key => $value) {
            $criteria[] = [
                $key,
                is_array($value) ? 'in' : '=',
                $value,
            ];
        }
        $qb = $this->eavFinder->getFilterByQb($options['family'], $criteria, $options['order_by'], $alias);

        $qb->distinct();

        return $qb;
    }

    /**
     * If a limit or an offset is specified, we are forced to use a paginator to handle joins properly
     *
     * @param ProcessState $state
     * @param string       $alias
     *
     * @throws \UnexpectedValueException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     *
     * @return Paginator
     */
    protected function getPaginator(ProcessState $state, $alias = 'e')
    {
        $options = $this->getOptions($state);
        /** @noinspection PhpDeprecationInspection */
        $paginator = new Paginator($this->getQueryBuilder($state, $alias));
        if (null !== $options['limit']) {
            $paginator->getQuery()->setMaxResults($options['limit']);
        }
        if (null !== $options['offset']) {
            $paginator->getQuery()->setFirstResult($options['offset']);
        }

        return $paginator;
    }
}
