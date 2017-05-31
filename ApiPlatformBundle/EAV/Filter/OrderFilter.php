<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\EAV\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Exception\MissingAttributeException;
use Sidus\EAVModelBundle\Model\AttributeInterface;

/**
 * Filter the collection by given properties.
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
        foreach ($orderProperties as $property => $value) {
            if (null !== $this->properties && !array_key_exists($property, $this->properties)) {
                return;
            }
            try {
                $attribute = $this->getAttribute($resourceClass, $property);
            } catch (MissingAttributeException $e) {
                return;
            }
            $this->filterAttribute(
                $queryBuilder,
                $attribute,
                $value,
                $this->properties[$property] ?? null,
                $operationName
            );
        }
    }

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
        $direction = strtoupper((empty($value) && $strategy) ? $strategy : $value);
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            return;
        }

        $eavQb = new EAVQueryBuilder($queryBuilder, 'o');
        $eavQb->addOrderBy($eavQb->attribute($attribute), $value);
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
        $description["order[{$property}]"] = [
            'property' => $property,
            'type' => $typeOfField,
            'required' => false,
            'strategy' => $strategy,
        ];
    }
}
