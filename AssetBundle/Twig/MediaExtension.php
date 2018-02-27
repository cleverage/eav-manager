<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
