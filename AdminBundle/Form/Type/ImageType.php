<?php

namespace CleverAge\EAVManager\AdminBundle\Form\Type;

use Sidus\FileUploadBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;

/**
 * Custom form type to upload images
 */
class ImageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'eavmanager_image';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
