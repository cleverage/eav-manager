<?php

namespace CleverAge\EAVManager\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class UserProfileType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('__tab_password', 'tab', [
            'label' => 'admin.user.tab.password.label',
            'inherit_data' => true,
        ]);
        $builder->get('__tab_password')
            ->add('plain_password', 'repeated', [
                'type' => 'password',
                'first_options' => array('label' => 'admin.user.form.password.label'),
                'second_options' => array('label' => 'admin.user.form.repeat_password.label'),
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'eavmanager_user_profile';
    }
}
