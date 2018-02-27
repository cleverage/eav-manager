<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Add the ability to create data directly in modal from a data selector.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DataSelectorTypeExtension extends AbstractTypeExtension
{
    /** @var string */
    protected $extendedType;

    /**
     * @param string $extendedType
     */
    public function __construct($extendedType)
    {
        $this->extendedType = $extendedType;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['data'] = $form->getData();
        $view->vars['allow_add'] = $options['allow_add'];
        $view->vars['allow_edit'] = $options['allow_edit'];
        $view->vars['allowed_families'] = $options['allowed_families'];
        $view->vars['admin'] = $options['admin'];
        $view->vars['action'] = $options['action'];
        $view->vars['target'] = $options['target'] ?: "tg_{$view->vars['id']}_modal";
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'allow_add' => false,
                'allow_edit' => false,
                'admin' => '_data',
                'action' => 'create',
                'target' => null,
            ]
        );
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return $this->extendedType;
    }
}
