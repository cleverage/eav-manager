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

use Sidus\AdminBundle\Routing\AdminRouter;
use Sidus\DataGridBundle\Form\Type\LinkType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Special type to create a link inside a form.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class AdminLink extends AbstractType
{
    /** @var AdminRouter */
    protected $adminRouter;

    /**
     * @param AdminRouter $adminRouter
     */
    public function __construct(AdminRouter $adminRouter)
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'action',
            ]
        );
        $resolver->setDefaults(
            [
                'admin' => null,
            ]
        );
        $resolver->setNormalizer(
            'uri',
            function (Options $options, $value) {
                if (null === $value) {
                    return $this->adminRouter->generateAdminPath(
                        $options['admin'],
                        $options['action'],
                        $options['route_parameters']
                    );
                }

                return $value;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_link';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return LinkType::class;
    }
}
