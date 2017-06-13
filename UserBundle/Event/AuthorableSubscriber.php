<?php

namespace CleverAge\EAVManager\UserBundle\Event;

use CleverAge\EAVManager\UserBundle\Entity\AuthorableInterface;
use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @see AuthorableInterface
 */
class AuthorableSubscriber implements EventSubscriber
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
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->preUpdate($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof AuthorableInterface) {
            return;
        }
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }
        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }
        if (!$entity->getCreatedBy()) {
            $entity->setCreatedBy($user);
        }
        $entity->setUpdatedBy($user);
    }
}
