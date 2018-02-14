<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
