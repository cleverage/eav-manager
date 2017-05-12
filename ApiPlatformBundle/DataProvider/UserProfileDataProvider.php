<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Provides access to family registry through Api Platform
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
     * @param string|null $operationName
     * @param int|string  $id
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
