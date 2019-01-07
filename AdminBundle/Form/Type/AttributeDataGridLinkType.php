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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Specific admin link for attribute datagrid create button
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class AttributeDataGridLinkType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setNormalizer(
            'uri',
            function (Options $options, $value) {
                return null;
            }
        );
        $resolver->setNormalizer(
            'route',
            function (Options $options, $value) {
                return null;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'attribute_datagrid_link';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AdminLinkType::class;
    }
}
