<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\EAV\Filter;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter as BaseSearchFilter;

/**
 * Filter the collection by given properties.
 */
class BooleanFilter extends AbstractEAVFilter
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
        if (in_array($value, ['true', '1'], true)) {
            $value = true;
        } elseif (in_array($value, ['false', '0'], true)) {
            $value = false;
        } else {
            $value = (bool) $value; // #covfefe
        }

        $eavQb = new EAVQueryBuilder($queryBuilder, 'o');
        $eavQb->apply($eavQb->attribute($attribute)->equals($value));
    }
}
