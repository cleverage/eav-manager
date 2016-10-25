<?php

namespace CleverAge\EAVManager\AssetBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class MediaBrowser extends AbstractType
{
    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['family'] = $options['family'];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'eavmanager_media_browser';
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'sidus_autocomplete_data_selector';
    }
}
