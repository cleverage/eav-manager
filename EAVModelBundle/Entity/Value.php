<?php

namespace CleverAge\EAVManager\EAVModelBundle\Entity;

use CleverAge\EAVManager\AssetBundle\Entity\Document;
use CleverAge\EAVManager\AssetBundle\Entity\Image;
use Doctrine\ORM\Mapping as ORM;
use Sidus\EAVModelBundle\Entity\Value as BaseValue;

abstract class Value extends BaseValue
{
    /**
     * @var Image
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\AssetBundle\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(name="image_value_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     */
    protected $imageValue;

    /**
     * @var Document
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\AssetBundle\Entity\Document", cascade={"persist"})
     * @ORM\JoinColumn(name="document_value_id", referencedColumnName="id", onDelete="cascade", nullable=true)
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
