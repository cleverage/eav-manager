<?php

namespace CleverAge\EAVManager\AssetBundle\Manager;

use Gaufrette\Exception\FileNotFound;
use Oneup\UploaderBundle\Uploader\File\GaufretteFile;
use Sidus\FileUploadBundle\Manager\ResourceManager as BaseResourceManager;
use Sidus\FileUploadBundle\Model\ResourceInterface;
use CleverAge\EAVManager\AssetBundle\Entity\Document;
use CleverAge\EAVManager\AssetBundle\Entity\Image;

class ResourceManager extends BaseResourceManager
{
    /**
     * Add an entry for Resource entity in database at each upload
     *
     * @param GaufretteFile $file
     * @param string $originalFilename
     * @param string $type
     * @return ResourceInterface
     * @throws \InvalidArgumentException|\UnexpectedValueException|FileNotFound
     */
    public function addFile(GaufretteFile $file, $originalFilename, $type = null)
    {
        $resource = $this->createByType($type)
            ->setOriginalFileName($originalFilename)
            ->setFileName($file->getKey());

        $this->updateResourceMetadata($resource, $file); // Custom code

        $em = $this->doctrine->getManager();
        $em->persist($resource);
        $em->flush();

        return $resource;
    }

    /**
     * @param ResourceInterface $resource
     * @param GaufretteFile $file
     * @throws \InvalidArgumentException|\UnexpectedValueException|FileNotFound
     */
    public function updateResourceMetadata(ResourceInterface $resource, GaufretteFile $file)
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
