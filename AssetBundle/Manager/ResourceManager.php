<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AssetBundle\Manager;

use CleverAge\EAVManager\AssetBundle\Entity\Document;
use CleverAge\EAVManager\AssetBundle\Entity\Image;
use League\Flysystem\File;
use Sidus\FileUploadBundle\Manager\ResourceManager as BaseResourceManager;
use Sidus\FileUploadBundle\Model\ResourceInterface;

/**
 * Extends the standard resource manager to append more info to the resource entities.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class ResourceManager extends BaseResourceManager
{
    /**
     * @param ResourceInterface $resource
     * @param File              $file
     *
     * @throws \UnexpectedValueException
     */
    protected function updateResourceMetadata(ResourceInterface $resource, File $file)
    {
        if ($resource instanceof Document) {
            $mimeType = $file->getMimetype();
            $resource
                ->setFileModifiedAt($file->getTimestamp())
                ->setFileSize($file->getSize())
                ->setMimeType($mimeType);
        }
        if ($resource instanceof Image) {
            $imageSize = getimagesizefromstring($file->read());
            $resource
                ->setWidth($imageSize[0] ?? null)
                ->setHeight($imageSize[1] ?? null);
        }
    }
}
