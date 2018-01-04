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

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Provides access to family registry through Api Platform.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UserProfileDataProvider implements ItemDataProviderInterface
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
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
     *
     * @return UserInterface|null
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        if ($id !== 'profile' || !is_a($resourceClass, UserInterface::class, true)) {
            throw new ResourceClassNotSupportedException();
        }

        $token = $this->tokenStorage->getToken();

        return $token->getUser();
    }
}
