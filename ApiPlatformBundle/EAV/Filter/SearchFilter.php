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

use ApiPlatform\Core\Exception\InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter as BaseSearchFilter;

/**
 * Filter the collection by given properties.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class SearchFilter extends AbstractEAVFilter
{
    /**
     * Passes a property through the filter.
     *
     * @param QueryBuilder       $queryBuilder
     * @param AttributeInterface $attribute
     * @param mixed              $value
     * @param null               $strategy
     * @param string|null        $operationName
     *
     * @throws \ApiPlatform\Core\Exception\InvalidArgumentException
     */
    protected function filterAttribute(
        QueryBuilder $queryBuilder,
        AttributeInterface $attribute,
        $value,
        $strategy = null,
        string $operationName = null
    ) {
        switch ($strategy) {
            case null:
            case BaseSearchFilter::STRATEGY_EXACT:
                $value = trim($value, '%');
                break;
            case BaseSearchFilter::STRATEGY_PARTIAL:
                $value = '%'.trim($value, '%').'%';
                break;
            case BaseSearchFilter::STRATEGY_START:
                $value = trim($value, '%').'%';
                break;
            case BaseSearchFilter::STRATEGY_END:
                $value = '%'.trim($value, '%');
                break;
            case BaseSearchFilter::STRATEGY_WORD_START:
                $value = '%'.trim($value, '%').'%'; // No, we are not going to implement this
                break;
            default:
                throw new InvalidArgumentException("strategy {$strategy} does not exist.");
        }

        $eavQb = new EAVQueryBuilder($queryBuilder, 'o');
        $eavQb->apply($eavQb->attribute($attribute)->like($value));
    }
}
