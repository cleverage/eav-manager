<?php

namespace CleverAge\EAVManager\AssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Sidus\FileUploadBundle\Entity\ResourceRepository")
 */
class Image extends Document
{
    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $width;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $height;

    /**
     * @return string
     */
    public static function getType()
    {
        return 'image';
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Serialize automatically the entity when passed to json_encode
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['width'] = $this->getWidth();
        $json['height'] = $this->getHeight();

        return $json;
    }
}
