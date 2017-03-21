<?php

namespace CleverAge\EAVManager\EAVModelBundle\Form\Extension;

use Sidus\EAVModelBundle\Form\Type\SimpleDataSelectorType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Add the ability to create data directly in modal from a data selector
 */
class DataSelectorTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_add'] = $options['allow_add'];
        $view->vars['allowed_families'] = $options['allowed_families'];
        $view->vars['admin'] = $options['admin'];
        $view->vars['action'] = $options['action'];
        $view->vars['target'] = $options['target'];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add' => false,
            'admin' => '_data',
            'action' => 'create',
            'target' => '#tg_modal',
        ]);
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return SimpleDataSelectorType::class;
    }
}
