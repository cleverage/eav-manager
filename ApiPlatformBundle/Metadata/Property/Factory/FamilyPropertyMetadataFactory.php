<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\Metadata\Property\Factory;

use ApiPlatform\Core\Exception\PropertyNotFoundException;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Used to change the identifier property of the family serializer.
 */
class FamilyPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /** @var PropertyMetadataFactoryInterface */
    protected $propertyMetadata;

    /**
     * @param PropertyMetadataFactoryInterface $propertyMetadata
     */
    public function __construct(PropertyMetadataFactoryInterface $propertyMetadata)
    {
        $this->propertyMetadata = $propertyMetadata;
    }

    /**
     * Creates a property metadata.
     *
     * @param string $resourceClass
     * @param string $property
     * @param array  $options
     *
     * @throws PropertyNotFoundException
     *
     * @return PropertyMetadata
     */
    public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata
    {
        $propertyMetadata = $this->propertyMetadata->create($resourceClass, $property, $options);

        if ('code' === $property && is_a($resourceClass, FamilyInterface::class, true)) {
            return $propertyMetadata->withIdentifier(true);
        }

        return $propertyMetadata;
    }
}
