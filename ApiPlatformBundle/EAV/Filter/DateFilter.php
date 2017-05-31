<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\EAV\Filter;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter as BaseDateFilter;

/**
 * Filter the collection by given properties.
 */
class DateFilter extends AbstractEAVFilter
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
        if (BaseDateFilter::EXCLUDE_NULL === $strategy) {
            $eavQb->apply($eavQb->attribute($attribute)->isNotNull());
        }

        if (isset($value[BaseDateFilter::PARAMETER_BEFORE])) {
            $handler = $eavQb->attribute($attribute)->lte($value[BaseDateFilter::PARAMETER_BEFORE]);
            if (BaseDateFilter::INCLUDE_NULL_BEFORE === $strategy) {
                $eavQb->getOr(
                    [
                        $handler,
                        $eavQb->attribute($attribute)->isNull(),
                    ]
                );
            }
            $eavQb->apply($handler);
        }

        if (isset($values[BaseDateFilter::PARAMETER_AFTER])) {
            $handler = $eavQb->attribute($attribute)->gte($value[BaseDateFilter::PARAMETER_AFTER]);
            if (BaseDateFilter::INCLUDE_NULL_AFTER === $strategy) {
                $eavQb->getOr(
                    [
                        $handler,
                        $eavQb->attribute($attribute)->isNull(),
                    ]
                );
            }
            $eavQb->apply($handler);
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
