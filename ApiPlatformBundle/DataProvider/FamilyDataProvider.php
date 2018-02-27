<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
