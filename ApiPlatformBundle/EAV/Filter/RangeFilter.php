<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\EAV\Filter;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter as BaseRangeFilter;

/**
 * Filter the collection by given properties.
 */
class RangeFilter extends AbstractEAVFilter
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
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function filterAttribute(
        QueryBuilder $queryBuilder,
        AttributeInterface $attribute,
        $value,
        $strategy = null,
        string $operationName = null
    ) {
        $eavQb = new EAVQueryBuilder($queryBuilder, 'o');
        if (isset($value[BaseRangeFilter::PARAMETER_BETWEEN])) {
            list($lower, $upper) = explode('..', $value[BaseRangeFilter::PARAMETER_BETWEEN]);
            $eavQb->apply($eavQb->attribute($attribute)->between($lower, $upper));

            return;
        }

        if (isset($value[BaseRangeFilter::PARAMETER_LESS_THAN])) {
            $eavQb->apply($eavQb->attribute($attribute)->lt($value[BaseRangeFilter::PARAMETER_LESS_THAN]));
        }

        if (isset($value[BaseRangeFilter::PARAMETER_LESS_THAN_OR_EQUAL])) {
            $eavQb->apply($eavQb->attribute($attribute)->lte($value[BaseRangeFilter::PARAMETER_LESS_THAN_OR_EQUAL]));
        }

        if (isset($value[BaseRangeFilter::PARAMETER_GREATER_THAN])) {
            $eavQb->apply($eavQb->attribute($attribute)->gt($value[BaseRangeFilter::PARAMETER_GREATER_THAN]));
        }

        if (isset($value[BaseRangeFilter::PARAMETER_GREATER_THAN_OR_EQUAL])) {
            $eavQb->apply($eavQb->attribute($attribute)->gte($value[BaseRangeFilter::PARAMETER_GREATER_THAN_OR_EQUAL]));
        }
    }

    /**
     * @param array $description
     * @param AttributeInterface $attribute
     * @param string $property
     * @param string $typeOfField
     * @param string $strategy
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
