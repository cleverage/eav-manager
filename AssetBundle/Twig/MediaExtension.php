<?php

namespace CleverAge\EAVManager\AssetBundle\Twig;

use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Sidus\FileUploadBundle\Utilities\BinarySizeUtility;
use Twig_Extension;
use CleverAge\EAVManager\AssetBundle\Entity\Image;

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

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('image_size_attrs', [$this, 'getImageSizeAttrs'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('file_size', [$this, 'formatFileSize']),
        ];
    }

    /**
     * @param Image $image
     * @param $filter
     * @return string
     * @throws NonExistingFilterException
     */
    public function getImageSizeAttrs(Image $image, $filter)
    {
        $config = $this->liipFilterConfiguration->get($filter);
        $width = $image->getWidth();
        $height = $image->getHeight();
        if (isset($config['filters']['thumbnail'])) {
            $width = $config['filters']['thumbnail']['size'][0];
            $height = $config['filters']['thumbnail']['size'][1];
            if ($config['filters']['thumbnail']['mode'] === 'inset') {
                if ($image->getWidth() > $image->getHeight()) {
                    $height = floor($width / $image->getWidth() * $image->getHeight());
                }
                if ($image->getWidth() < $image->getHeight()) {
                    $width = floor($height / $image->getHeight() * $image->getWidth());
                }
            }
        }

        return strtr('width="%w%" height="%h%" alt="%a%"', [
            '%w%' => $width,
            '%h%' => $height,
            '%a%' => $image->getOriginalFileName(),
        ]);
    }

    /**
     * @param int $size
     * @return string
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
