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
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allowed_families'] = $options['allowed_families'];
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
