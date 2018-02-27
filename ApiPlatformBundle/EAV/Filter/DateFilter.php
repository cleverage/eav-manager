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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter as BaseDateFilter;

/**
 * Filter the collection by given properties.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DateFilter extends AbstractEAVFilter
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
        if (BaseDateFilter::EXCLUDE_NULL === $strategy) {
            $dqlHandlers[] = $attributeQueryBuilder->isNotNull();
        }

        if (isset($value[BaseDateFilter::PARAMETER_BEFORE])) {
            $handler = $attributeQueryBuilder->lte($value[BaseDateFilter::PARAMETER_BEFORE]);
            if (BaseDateFilter::INCLUDE_NULL_BEFORE === $strategy) {
                $eavQb->getOr(
                    [
                        $handler,
                        $attributeQueryBuilder->isNull(),
                    ]
                );
            }
            $dqlHandlers[] = $handler;
        }

        if (isset($values[BaseDateFilter::PARAMETER_AFTER])) {
            $handler = $attributeQueryBuilder->gte($value[BaseDateFilter::PARAMETER_AFTER]);
            if (BaseDateFilter::INCLUDE_NULL_AFTER === $strategy) {
                $eavQb->getOr(
                    [
                        $handler,
                        $attributeQueryBuilder->isNull(),
                    ]
                );
            }
            $dqlHandlers[] = $handler;
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
        foreach ([BaseDateFilter::PARAMETER_BEFORE, BaseDateFilter::PARAMETER_AFTER] as $filterParameterName) {
            $description["{$property}[{$filterParameterName}]"] = [
                'property' => $property,
                'type' => $typeOfField,
                'required' => false,
                'strategy' => $strategy,
            ];
        }
    }
}
