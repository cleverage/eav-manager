<?php

namespace CleverAge\EAVManager\SecurityBundle\Form\Type;

use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FamilyPermissionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('family', 'sidus_family_selector', [
            'label' => false,
            'horizontal_input_wrapper_class' => 'col-sm-3',
        ]);
        foreach (FamilyPermission::getPermissions() as $permission) {
            $builder->add($permission, 'checkbox', [
                'widget_checkbox_label' => 'widget',
                'horizontal_input_wrapper_class' => 'col-sm-1',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission',
            'required' => false,
            'widget_type' => 'inline',
        ]);
    }


    public function getName()
    {
        return 'family_permission';
    }
}
