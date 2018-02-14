<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sidus\EAVModelBundle\Utilities\DateTimeUtility;
use Sidus\FileUploadBundle\Entity\Resource;

/**
 * @ORM\Entity(repositoryClass="Sidus\FileUploadBundle\Entity\ResourceRepository")
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class Document extends Resource
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $fileSize;

    /**
     * Mime type.
     *
     * @var string
     *
     * @ORM\Column(name="mime_type", type="string", length=128, nullable=true)
     */
    protected $mimeType;

    /**
     * File's last modification date.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="file_modified_at", type="datetime", nullable=true)
     */
    protected $fileModifiedAt;

    /**
     * @return string
     */
    public static function getType()
    {
        return 'document';
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @param int $fileSize
     *
     * @return Document
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     *
     * @return Document
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFileModifiedAt()
    {
        return $this->fileModifiedAt;
    }

    /**
     * @param \DateTime|int|string|null $fileModifiedAt
     *
     * @return Document
     *
     * @throws \UnexpectedValueException
     */
    public function setFileModifiedAt($fileModifiedAt)
    {
        $this->fileModifiedAt = DateTimeUtility::parse($fileModifiedAt);

        return $this;
    }

    /**
     * Serialize automatically the entity when passed to json_encode.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['fileSize'] = $this->getFileSize();
        $json['mimeType'] = $this->getMimeType();
        $json['fileModifiedAt'] = $this->getFileModifiedAt();
        $json['type'] = static::getType();

        return $json;
    }
}
