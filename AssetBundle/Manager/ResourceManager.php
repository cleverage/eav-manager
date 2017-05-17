<?php

namespace CleverAge\EAVManager\AssetBundle\Manager;

use CleverAge\EAVManager\AssetBundle\Entity\Document;
use CleverAge\EAVManager\AssetBundle\Entity\Image;
use League\Flysystem\File;
use Sidus\FileUploadBundle\Manager\ResourceManager as BaseResourceManager;
use Sidus\FileUploadBundle\Model\ResourceInterface;

/**
 * Extends the standard resource manager to append more info to the resource entities.
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
                ->setWidth(isset($imageSize[0]) ? $imageSize[0] : null)
                ->setHeight(isset($imageSize[1]) ? $imageSize[1] : null);
        }
    }
}
