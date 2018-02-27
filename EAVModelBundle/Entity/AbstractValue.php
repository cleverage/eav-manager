<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\EAVModelBundle\Entity;

use CleverAge\EAVManager\AssetBundle\Entity\Document;
use CleverAge\EAVManager\AssetBundle\Entity\Image;
use Doctrine\ORM\Mapping as ORM;
use Sidus\EAVModelBundle\Entity\AbstractValue as AbstractBaseValue;

/**
 * Adding relations to images and documents to values.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
abstract class AbstractValue extends AbstractBaseValue
{
    /**
     * @var Image
     *
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\AssetBundle\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(name="image_value_id", referencedColumnName="id", nullable=true)
     */
    protected $imageValue;

    /**
     * @var Document
     *
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\AssetBundle\Entity\Document", cascade={"persist"})
     * @ORM\JoinColumn(name="document_value_id", referencedColumnName="id", nullable=true)
     */
    protected $documentValue;

    /**
     * @return Image
     */
    public function getImageValue()
    {
        return $this->imageValue;
    }

    /**
     * @param Image $imageValue
     */
    public function setImageValue(Image $imageValue = null)
    {
        $this->imageValue = $imageValue;
    }

    /**
     * @return Document
     */
    public function getDocumentValue()
    {
        return $this->documentValue;
    }

    /**
     * @param Document $documentValue
     */
    public function setDocumentValue(Document $documentValue = null)
    {
        $this->documentValue = $documentValue;
    }
}
