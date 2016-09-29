<?php

namespace CleverAge\EAVManager\AdminBundle\Form\Type;

use Sidus\FileUploadBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;

class ImageType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'eavmanager_image';
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
