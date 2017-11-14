<?php

namespace CleverAge\EAVManager\EAVModelBundle\Resolver;

use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;

/**
 * Tries to resolve a family from it's data class, won't work with families using the generic Data entity
 */
class FamilyResolver
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * @param FamilyRegistry $familyRegistry
     */
    public function __construct(FamilyRegistry $familyRegistry)
    {
        $this->familyRegistry = $familyRegistry;
    }

    /**
     * @param string $resourceClass
     *
     * @throws \LogicException
     *
     * @return FamilyInterface
     */
    public function getFamily(string $resourceClass): FamilyInterface
    {
        $matchingFamilies = $this->getFamilies($resourceClass);

        if (1 === \count($matchingFamilies)) {
            return reset($matchingFamilies);
        }

        throw new \LogicException("Cannot resolve family for class '{$resourceClass}'");
    }

    /**
     * @param string $resourceClass
     *
     * @return FamilyInterface[]
     */
    public function getFamilies(string $resourceClass): array
    {
        $matchingFamilies = [];
        foreach ($this->familyRegistry->getFamilies() as $family) {
            if (ltrim($family->getDataClass(), '\\') === ltrim($resourceClass, '\\')) {
                $matchingFamilies[] = $family;
            }
        }

        return $matchingFamilies;
    }
}
