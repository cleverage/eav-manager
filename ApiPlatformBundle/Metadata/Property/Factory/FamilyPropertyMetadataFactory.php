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

use ApiPlatform\Core\Exception\PropertyNotFoundException;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Used to change the identifier property of the family serializer.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
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
