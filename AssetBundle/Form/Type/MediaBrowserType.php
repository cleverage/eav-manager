<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AssetBundle\Form\Type;

use Sidus\EAVBootstrapBundle\Form\Type\AutocompleteDataSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Form type to browse media data.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class MediaBrowserType extends AbstractType
{
    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allowed_families'] = $options['allowed_families'];
        $view->vars['eavData'] = $form->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'eavmanager_media_browser';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AutocompleteDataSelectorType::class;
    }
}
