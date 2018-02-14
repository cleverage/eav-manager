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

use ApiPlatform\Core\Exception\InvalidArgumentException;
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter as BaseSearchFilter;

/**
 * Filter the collection by given properties.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class SearchFilter extends AbstractEAVFilter
{
    /**
     * {@inheritdoc}
     *
     * @throws \ApiPlatform\Core\Exception\InvalidArgumentException
     */
    protected function filterAttribute(
        EAVQueryBuilderInterface $eavQb,
        AttributeQueryBuilderInterface $attributeQueryBuilder,
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

        return $attributeQueryBuilder->like($value);
    }
}
