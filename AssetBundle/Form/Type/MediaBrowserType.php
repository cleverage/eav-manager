<?php

namespace CleverAge\EAVManager\AssetBundle\Form\Type;

use Sidus\EAVBootstrapBundle\Form\Type\AutocompleteDataSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Form type to browse media data
 */
class MediaBrowserType extends AbstractType
{
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
