<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Form\Type;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * User profile.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
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

        $builder->add(
            '__tab_password',
            TabType::class,
            [
                'label' => 'admin.user.tab.password.label',
                'inherit_data' => true,
            ]
        );
        /** @var FormBuilderInterface $passwordTab */
        $passwordTab = $builder->get('__tab_password');
        $passwordTab
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'admin.user.form.password.label'],
                    'second_options' => ['label' => 'admin.user.form.repeat_password.label'],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'eavmanager_user_profile';
    }
}
