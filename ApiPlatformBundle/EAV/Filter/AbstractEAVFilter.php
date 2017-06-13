<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\EAV\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Exception\MissingAttributeException;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter as BaseSearchFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Abstract class with helpers for easing the implementation of a filter.
 */
abstract class AbstractEAVFilter implements FilterInterface
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var array */
    protected $supportedTypes;

    /** @var array */
    protected $properties;

    /** @var string */
    protected $familyCode;

    /**
     * @param RequestStack   $requestStack
     * @param FamilyRegistry $familyRegistry
     * @param array          $supportedTypes
     * @param array          $properties
     * @param string         $familyCode
     */
    public function __construct(
        RequestStack $requestStack,
        FamilyRegistry $familyRegistry,
        array $supportedTypes,
        array $properties = null,
        $familyCode = null
    ) {
        $this->requestStack = $requestStack;
        $this->familyRegistry = $familyRegistry;
        $this->supportedTypes = $supportedTypes;
        $this->properties = $properties;
        $this->familyCode = $familyCode;
    }

    /**
     * {@inheritdoc}
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     * @throws \UnexpectedValueException
     * @throws \LogicException
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

        foreach ($this->extractProperties($request) as $property => $value) {
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
     * Gets the description of this filter for the given resource.
     *
     * Returns an array with the filter parameter names as keys and array with the following data as values:
     *   - property: the property where the filter is applied
     *   - type: the type of the filter
     *   - required: if this filter is required
     *   - strategy: the used strategy
     *   - swagger (optional): additional parameters for the path operation, e.g. 'swagger' => ['description' => 'My
     *   Description'] The description can contain additional data specific to a filter.
     *
     * @param string $resourceClass
     *
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->properties;
        if (null === $properties) {
            $family = $this->getFamily($resourceClass);
            $properties = array_fill_keys(array_keys($family->getAttributes()), null);
        }

        foreach ($properties as $property => $strategy) {
            try {
                $attribute = $this->getAttribute($resourceClass, $property);
            } catch (MissingAttributeException $e) {
                continue;
            }
            $typeOfField = $this->getType($attribute);
            if (!in_array($typeOfField, $this->supportedTypes, true)) {
                continue;
            }

            $this->appendFilterDescription($description, $attribute, $property, $typeOfField, $strategy);
        }

        return $description;
    }

    /**
     * Passes a property through the filter.
     *
     * @param QueryBuilder       $queryBuilder
     * @param AttributeInterface $attribute
     * @param mixed              $value
     * @param null               $strategy
     * @param string|null        $operationName
     */
    abstract protected function filterAttribute(
        QueryBuilder $queryBuilder,
        AttributeInterface $attribute,
        $value,
        $strategy = null,
        string $operationName = null
    );

    /**
     * Extracts properties to filter from the request.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function extractProperties(Request $request): array
    {
        return $request->query->all();
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
        if ($attribute->getType()->isRelation() || $attribute->getType()->isEmbedded()) {
            $filterParameterNames = [
                $property,
                $property.'[]',
            ];

            foreach ($filterParameterNames as $filterParameterName) {
                $description[$filterParameterName] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'strategy' => BaseSearchFilter::STRATEGY_EXACT,
                ];
            }
        }

        $strategy = $strategy ?: BaseSearchFilter::STRATEGY_EXACT;
        $filterParameterNames = [$property];

        if (BaseSearchFilter::STRATEGY_EXACT === $strategy) {
            $filterParameterNames[] = $property.'[]';
        }

        foreach ($filterParameterNames as $filterParameterName) {
            $description[$filterParameterName] = [
                'property' => $property,
                'type' => $typeOfField,
                'required' => false,
                'strategy' => $strategy,
            ];
        }
    }

    /**
     * Converts an EAV type in PHP type.
     *
     * @param AttributeInterface $attribute
     *
     * @return string
     */
    protected function getType(AttributeInterface $attribute): string
    {
        switch ($attribute->getType()->getDatabaseType()) {
            case 'integerValue':
                return 'int';
            case 'boolValue':
                return 'bool';
            case 'dateValue':
            case 'datetimeValue':
                return \DateTimeInterface::class;
            case 'decimalValue':
                return 'float';
            case 'stringValue':
            case 'textValue':
                return 'string';
            case 'dataValue':
                return DataInterface::class;
        }

        return 'mixed';
    }

    /**
     * @param string $resourceClass
     * @param string $property
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     *
     * @return AttributeInterface
     */
    protected function getAttribute(string $resourceClass, $property): AttributeInterface
    {
        $family = $this->getFamily($resourceClass);
        if (!$family->hasAttribute($property)) {
            if ($property === 'label') {
                return $family->getAttributeAsLabel();
            }
            if ($property === 'identifier') {
                return $family->getAttributeAsIdentifier();
            }
        }

        return $family->getAttribute($property);
    }

    /**
     * @param string $resourceClass
     *
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     * @throws \UnexpectedValueException
     *
     * @return FamilyInterface
     */
    protected function getFamily(string $resourceClass): FamilyInterface
    {
        if ($this->familyCode) {
            $family = $this->familyRegistry->getFamily($this->familyCode);
            if (ltrim($family->getDataClass(), '\\') !== ltrim($resourceClass, '\\')) {
                throw new \UnexpectedValueException("Resource class '{$resourceClass}' not matching family for filter");
            }

            return $family;
        }

        $matchingFamilies = [];
        foreach ($this->familyRegistry->getFamilies() as $family) {
            if (ltrim($family->getDataClass(), '\\') === ltrim($resourceClass, '\\')) {
                $matchingFamilies[] = $family;
            }
        }

        if (1 === count($matchingFamilies)) {
            return reset($matchingFamilies);
        }

        throw new \LogicException("Cannot resolve family for class '{$resourceClass}'");
    }
}
