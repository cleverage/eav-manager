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

use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter as BaseRangeFilter;

/**
 * Filter the collection by given properties.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class RangeFilter extends AbstractEAVFilter
{
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
        $dqlHandlers = [];
        if (isset($value[BaseRangeFilter::PARAMETER_BETWEEN])) {
            list($lower, $upper) = explode('..', $value[BaseRangeFilter::PARAMETER_BETWEEN]);

            return $attributeQueryBuilder->between($lower, $upper);
        }

        if (isset($value[BaseRangeFilter::PARAMETER_LESS_THAN])) {
            $dqlHandlers[] = $attributeQueryBuilder->lt($value[BaseRangeFilter::PARAMETER_LESS_THAN]);
        }

        if (isset($value[BaseRangeFilter::PARAMETER_LESS_THAN_OR_EQUAL])) {
            $dqlHandlers[] = $attributeQueryBuilder->lte($value[BaseRangeFilter::PARAMETER_LESS_THAN_OR_EQUAL]);
        }

        if (isset($value[BaseRangeFilter::PARAMETER_GREATER_THAN])) {
            $dqlHandlers[] = $attributeQueryBuilder->gt($value[BaseRangeFilter::PARAMETER_GREATER_THAN]);
        }

        if (isset($value[BaseRangeFilter::PARAMETER_GREATER_THAN_OR_EQUAL])) {
            $dqlHandlers[] = $attributeQueryBuilder->gte($value[BaseRangeFilter::PARAMETER_GREATER_THAN_OR_EQUAL]);
        }

        return $eavQb->getAnd($dqlHandlers);
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
        $parameters = [
            BaseRangeFilter::PARAMETER_BETWEEN,
            BaseRangeFilter::PARAMETER_GREATER_THAN,
            BaseRangeFilter::PARAMETER_GREATER_THAN_OR_EQUAL,
            BaseRangeFilter::PARAMETER_LESS_THAN,
            BaseRangeFilter::PARAMETER_LESS_THAN_OR_EQUAL,
        ];

        foreach ($parameters as $filterParameterName) {
            $description["{$property}[{$filterParameterName}]"] = [
                'property' => $property,
                'type' => $typeOfField,
                'required' => false,
                'strategy' => $strategy,
            ];
        }
    }
}
