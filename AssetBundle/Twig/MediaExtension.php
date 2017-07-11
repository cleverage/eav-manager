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

namespace CleverAge\EAVManager\AssetBundle\Twig;

use CleverAge\EAVManager\AssetBundle\Entity\Image;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Sidus\FileUploadBundle\Utilities\BinarySizeUtility;
use Twig_Extension;

/**
 * Allows twig templates to gather information about image size.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class MediaExtension extends Twig_Extension
{
    /** @var FilterConfiguration */
    protected $liipFilterConfiguration;

    /**
     * @param FilterConfiguration $liipFilterConfiguration
     */
    public function __construct(FilterConfiguration $liipFilterConfiguration)
    {
        $this->liipFilterConfiguration = $liipFilterConfiguration;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('image_size_attrs', [$this, 'getImageSizeAttrs'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('file_size', [$this, 'formatFileSize']),
        ];
    }

    /**
     * @param Image  $image
     * @param string $filter
     *
     * @return string
     *
     * @throws NonExistingFilterException
     */
    public function getImageSizeAttrs(Image $image, $filter)
    {
        $config = $this->liipFilterConfiguration->get($filter);
        $width = $image->getWidth();
        $height = $image->getHeight();
        if (isset($config['filters']['thumbnail'])) {
            list($width, $height) = $config['filters']['thumbnail']['size'];
            if ($config['filters']['thumbnail']['mode'] === 'inset') {
                if ($image->getWidth() >= $image->getHeight()) {
                    $height = floor($width / $image->getWidth() * $image->getHeight());
                }
                if ($image->getWidth() <= $image->getHeight()) {
                    $width = floor($height / $image->getHeight() * $image->getWidth());
                }
            }
        }

        return strtr(
            'width="%w%" height="%h%"',
            [
                '%w%' => $width,
                '%h%' => $height,
            ]
        );
    }

    /**
     * @param int $size
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    public function formatFileSize($size)
    {
        return BinarySizeUtility::format($size, 2, ',', '', ' ');
    }

    /**
     * @return string The extension name
     */
    public function getName()
    {
        return 'eavmanager_media';
    }
}
