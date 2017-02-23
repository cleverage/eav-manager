<?php

namespace CleverAge\EAVManager\AssetBundle\Manager;

use CleverAge\EAVManager\AssetBundle\Entity\Document;
use CleverAge\EAVManager\AssetBundle\Entity\Image;
use Gaufrette\Exception\FileNotFound;
use Oneup\UploaderBundle\Uploader\File\GaufretteFile;
use Sidus\FileUploadBundle\Manager\ResourceManager as BaseResourceManager;
use Sidus\FileUploadBundle\Model\ResourceInterface;

/**
 * Extends the standard resource manager to append more info to the resource entities
 */
class ResourceManager extends BaseResourceManager
{
    /**
     * @param ResourceInterface $resource
     * @param GaufretteFile     $file
     *
     * @throws \InvalidArgumentException|\UnexpectedValueException|FileNotFound
     */
    protected function updateResourceMetadata(ResourceInterface $resource, GaufretteFile $file)
    {
        if ($resource instanceof Document) {
            $mimeType = $file->getMimeType();
            if (!$mimeType) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($file->getContent());
            }
            $resource
                ->setFileModifiedAt($file->getMtime())
                ->setFileSize($file->getSize())
                ->setFileType($mimeType);
        }
        if ($resource instanceof Image) {
            $imageSize = getimagesizefromstring($file->getContent());
            $resource
                ->setWidth(isset($imageSize[0]) ? $imageSize[0] : null)
                ->setHeight(isset($imageSize[1]) ? $imageSize[1] : null);
        }
    }
}
