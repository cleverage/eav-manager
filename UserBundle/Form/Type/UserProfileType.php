<?php

namespace CleverAge\EAVManager\UserBundle\Form\Type;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class UserProfileType extends UserType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('__tab_password', TabType::class, [
            'label' => 'admin.user.tab.password.label',
            'inherit_data' => true,
        ]);
        $builder->get('__tab_password')
            ->add('plain_password', RepeatedType::class, [
                'type' => 'password',
                'first_options' => array('label' => 'admin.user.form.password.label'),
                'second_options' => array('label' => 'admin.user.form.repeat_password.label'),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'eavmanager_user_profile';
    }
}
