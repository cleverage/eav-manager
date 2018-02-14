<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Form\Type;

use Sidus\FileUploadBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;

/**
 * Custom form type to upload images.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
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
