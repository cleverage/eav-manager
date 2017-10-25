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
