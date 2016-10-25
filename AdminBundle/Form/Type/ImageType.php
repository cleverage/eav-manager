<?php

namespace CleverAge\EAVManager\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class ImageType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'eavmanager_image';
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'sidus_resource';
    }
}
