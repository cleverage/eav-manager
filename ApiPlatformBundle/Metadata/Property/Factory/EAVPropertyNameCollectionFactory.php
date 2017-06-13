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

namespace CleverAge\EAVManager\ApiPlatformBundle\Metadata\Property\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyNameCollection;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;

/**
 * Overriding property name collection factory for EAV data to remove "values" and inject EAV attributes.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class EAVPropertyNameCollectionFactory implements PropertyNameCollectionFactoryInterface
{
    /** @var PropertyNameCollectionFactoryInterface */
    protected $propertyNameCollectionFactory;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var array */
    protected $ignoredAttributes;

    /**
     * @param PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory
     * @param FamilyRegistry                         $familyRegistry
     * @param array                                  $ignoredAttributes
     */
    public function __construct(
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
        FamilyRegistry $familyRegistry,
        array $ignoredAttributes
    ) {
        $this->familyRegistry = $familyRegistry;
        $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
        $this->ignoredAttributes = $ignoredAttributes;
    }

    /**
     * Creates the property name collection for the given class and options.
     *
     * @param string $resourceClass
     * @param array  $options
     *
     * @throws ResourceClassNotFoundException
     *
     * @return PropertyNameCollection
     */
    public function create(string $resourceClass, array $options = []): PropertyNameCollection
    {
        $propertyNameCollection = $this->propertyNameCollectionFactory->create($resourceClass, $options);
        if (is_a($resourceClass, DataInterface::class, true)) {
            $resolvedProperties = [];
            foreach ($propertyNameCollection as $propertyName) {
                if (!in_array($propertyName, $this->ignoredAttributes, true)) {
                    $resolvedProperties[] = $propertyName;
                }
            }
            $propertyNameCollection = new PropertyNameCollection($resolvedProperties);
        }

        return $propertyNameCollection;
    }
}
