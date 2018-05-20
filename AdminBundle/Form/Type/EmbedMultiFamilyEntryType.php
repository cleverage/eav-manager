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

use Doctrine\Common\Persistence\ManagerRegistry;
use Sidus\EAVBootstrapBundle\Form\Type\AutocompleteDataSelectorType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Very similar to the behavior of an embed type but allowing multi-families
 */
class EmbedMultiFamilyEntryType extends AutocompleteDataSelectorType
{
    /**
     * @param ManagerRegistry $doctrine
     * @param string          $dataClass
     */
    public function __construct(
        ManagerRegistry $doctrine,
        $dataClass
    ) {
        $this->repository = $doctrine->getRepository($dataClass);
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'max_results' => 0,
                'choices' => [],
                'choice_loader' => null,
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'embed_multi_family_entry';
    }
}
