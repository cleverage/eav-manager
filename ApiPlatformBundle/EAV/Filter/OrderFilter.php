<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\ApiPlatformBundle\EAV\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;

/**
 * Filter the collection by given properties.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class OrderFilter extends AbstractEAVFilter
{
    /**
     * {@inheritdoc}
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \ApiPlatform\Core\Exception\InvalidArgumentException
     */
    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }
        $requestProperties = $this->extractProperties($request);
        if (!array_key_exists('order', $requestProperties)) {
            return;
        }

        /** @var array $orderProperties */
        $orderProperties = $requestProperties['order'];
        $this->doApply($queryBuilder, $orderProperties, $resourceClass, $operationName);
    }

    /**
     * {@inheritdoc}
     */
    protected function filterAttribute(
        EAVQueryBuilderInterface $eavQb,
        AttributeQueryBuilderInterface $attributeQueryBuilder,
        $value,
        $strategy = null,
        string $operationName = null
    ) {
        $direction = strtoupper((empty($value) && $strategy) ? $strategy : $value);
        if (!\in_array($direction, ['ASC', 'DESC'], true)) {
            return;
        }

        $eavQb->addOrderBy($attributeQueryBuilder, $value);
    }

    /**
     * @param array              $description
     * @param AttributeInterface $attribute
     * @param string             $property
     * @param string             $typeOfField
     * @param string             $strategy
     */
    protected function appendFilterDescription(
        array &$description,
        AttributeInterface $attribute,
        $property,
        $typeOfField,
        $strategy = null
    ) {
        $description["order[{$property}]"] = [
            'property' => $property,
            'type' => $typeOfField,
            'required' => false,
            'strategy' => $strategy,
        ];
    }
}
