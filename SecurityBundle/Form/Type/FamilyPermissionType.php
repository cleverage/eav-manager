<?php

namespace CleverAge\EAVManager\SecurityBundle\Form\Type;

use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use Sidus\EAVModelBundle\Form\Type\FamilySelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Edit families permissions.
 */
class FamilyPermissionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'family',
            FamilySelectorType::class,
            [
                'label' => false,
                'horizontal_input_wrapper_class' => 'col-sm-3',
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
