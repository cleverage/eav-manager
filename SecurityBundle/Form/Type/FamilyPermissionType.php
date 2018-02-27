<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\SecurityBundle\Form\Type;

use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use Sidus\EAVModelBundle\Form\Type\FamilySelectorType;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Edit families permissions.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class FamilyPermissionType extends AbstractType
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * @param FamilyRegistry $familyRegistry
     */
    public function __construct(FamilyRegistry $familyRegistry)
    {
        $this->familyRegistry = $familyRegistry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'familyCode',
            FamilySelectorType::class,
            [
                'label' => false,
                'placeholder' => 'permission.family.placeholder',
                'horizontal_input_wrapper_class' => 'col-sm-12',
            ]
        );
        foreach (FamilyPermission::getPermissions() as $permission) {
            $builder->add(
                $permission,
                CheckboxType::class,
                [
                    'widget_checkbox_label' => 'widget',
                    'horizontal_input_wrapper_class' => 'col-sm-1',
                ]
            );
        }

        $builder->get('familyCode')->addModelTransformer(
            new CallbackTransformer(
                function ($originalData) {
                    if (null === $originalData) {
                        return null;
                    }
                    // Ignoring missing family
                    if (!$this->familyRegistry->hasFamily($originalData)) {
                        return null;
                    }

                    return $this->familyRegistry->getFamily($originalData);
                },
                function ($submittedData) {
                    if ($submittedData instanceof FamilyInterface) {
                        return $submittedData->getCode();
                    }

                    return $submittedData;
                }
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => FamilyPermission::class,
                'required' => false,
                'widget_type' => 'inline',
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'family_permission';
    }
}
