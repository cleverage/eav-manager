<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\AssetBundle\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(name="image_value_id", referencedColumnName="id", nullable=true)
     */
    protected $imageValue;

    /**
     * @var Document
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
