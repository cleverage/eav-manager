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
