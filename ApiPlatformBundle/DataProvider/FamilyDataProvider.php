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

namespace CleverAge\EAVManager\ApiPlatformBundle\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;

/**
 * Provides access to family registry through Api Platform.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class FamilyDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface
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
     * Retrieves a collection.
     *
     * @param string      $resourceClass
     * @param string|null $operationName
     *
     * @throws ResourceClassNotSupportedException
     *
     * @return FamilyInterface[]|PaginatorInterface|\Traversable
     */
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        if (!is_a($resourceClass, FamilyInterface::class, true)) {
            throw new ResourceClassNotSupportedException();
        }

        return $this->familyRegistry->getFamilies();
    }

    /**
     * Retrieves an item.
     *
     * @param string      $resourceClass
     * @param int|string  $id
     * @param string|null $operationName
     * @param array       $context
     *
     * @throws ResourceClassNotSupportedException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     *
     * @return FamilyInterface|null
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        if (!is_a($resourceClass, FamilyInterface::class, true)) {
            throw new ResourceClassNotSupportedException();
        }

        return $this->familyRegistry->getFamily($id);
    }
}
