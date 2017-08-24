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

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;

/**
 * Filter the collection by given properties.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class NumericFilter extends AbstractEAVFilter
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
        return $attributeQueryBuilder->equals($value);
    }
}
