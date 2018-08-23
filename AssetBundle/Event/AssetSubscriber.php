<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AssetBundle\Event;

use CleverAge\EAVManager\AssetBundle\Entity\Document;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\ValueInterface;

/**
 * Doctrine subscriber to fill certain values normally held by the Resource entity in the EAV model (for indexing).
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class AssetSubscriber implements EventSubscriber
{
    /** @var array */
    protected $familyMap;

    /** @var ValueInterface[] */
    protected $valuesToPersist = [];

    /**
     * @param array $familyMap
     */
    public function __construct(array $familyMap)
    {
        $this->familyMap = $familyMap;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
            Events::prePersist,
            Events::postFlush,
        ];
    }

    /**
     * @param PreUpdateEventArgs $event
     *
     * @throws \Exception
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $this->updateEntity($event->getEntity());
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $this->updateEntity($event->getEntity());
    }

    /**
     * @param PostFlushEventArgs $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $valueCount = \count($this->valuesToPersist);
        foreach ($this->valuesToPersist as $value) {
            $em->persist($value);
        }
        $this->valuesToPersist = [];
        if ($valueCount > 0) {
            $em->flush();
        }
    }

    /**
     * @param DataInterface|mixed $data
     *
     * @throws \Exception
     */
    protected function updateEntity($data)
    {
        if (!$data instanceof DataInterface) {
            return;
        }
        $family = $data->getFamily();

        if (!array_key_exists($data->getFamilyCode(), $this->familyMap)) {
            return;
        }
        $resource = $data->get($this->familyMap[$data->getFamilyCode()]);
        if (!$resource instanceof Document) {
            // Maybe we should reset the values to null
            return;
        }
        $data->setFileSize($resource->getFileSize());
        $this->valuesToPersist[] = $data->getValue($family->getAttribute('fileSize'));

        $data->setMimeType($resource->getMimeType());
        $this->valuesToPersist[] = $data->getValue($family->getAttribute('mimeType'));

        $data->setFileName($resource->getOriginalFileName());
        $this->valuesToPersist[] = $data->getValue($family->getAttribute('fileName'));
    }
}
