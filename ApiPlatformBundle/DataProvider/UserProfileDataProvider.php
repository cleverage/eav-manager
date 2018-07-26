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
        if ('profile' !== $id || !is_a($resourceClass, UserInterface::class, true)) {
            throw new ResourceClassNotSupportedException();
        }

        $token = $this->tokenStorage->getToken();

        /** @var UserInterface|null $user */
        $user = null;
        if ($token) {
            $user = $token->getUser();
        }

        return $user;
    }
}
