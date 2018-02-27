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

use CleverAge\EAVManager\SecurityBundle\Form\Type\FamilyPermissionType;
use CleverAge\EAVManager\SecurityBundle\Form\Type\RoleHierarchyType;
use CleverAge\EAVManager\UserBundle\Entity\Group;
use CleverAge\EAVManager\UserBundle\Entity\User;
use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Sidus\EAVBootstrapBundle\Form\Type\BootstrapCollectionType;
use Sidus\EAVBootstrapBundle\Form\Type\SwitchType;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Form\Type\DataType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * User edition form.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UserType extends AbstractType
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param FamilyRegistry                $familyRegistry
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        FamilyRegistry $familyRegistry,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->familyRegistry = $familyRegistry;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            '__tab_main',
            TabType::class,
            [
                'label' => 'admin.user.tab.main.label',
                'inherit_data' => true,
            ]
        );
        /** @var FormBuilderInterface $mainTab */
        $mainTab = $builder->get('__tab_main');
        $mainTab
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'admin.user.form.username.label',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'admin.user.form.email.label',
                ]
            );

        if ($this->getUser() && $this->authorizationChecker->isGranted('ROLE_ADMIN', $this->getUser())) {
            $mainTab
                ->add(
                    'enabled',
                    SwitchType::class,
                    [
                        'label' => 'admin.user.form.enabled.label',
                    ]
                )
                ->add(
                    'rawRoles',
                    RoleHierarchyType::class,
                    [
                        'label' => 'admin.user.form.roles.label',
                    ]
                )
                ->add(
                    'familyPermissions',
                    BootstrapCollectionType::class,
                    [
                        'label' => 'admin.user.form.familyPermissions.label',
                        'entry_type' => FamilyPermissionType::class,
                        'entry_options' => [],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'required' => false,
                        'sortable' => false,
                        'by_reference' => false,
                    ]
                );

            $builder->add(
                '__tab_groups',
                TabType::class,
                [
                    'label' => 'admin.user.tab.groups.label',
                    'inherit_data' => true,
                ]
            );
            /** @var FormBuilderInterface $groupsTab */
            $groupsTab = $builder->get('__tab_groups');
            $groupsTab
                ->add(
                    'groups',
                    EntityType::class,
                    [
                        'label' => 'admin.user.form.groups.label',
                        'class' => Group::class,
                        'expanded' => true,
                        'multiple' => true,
                    ]
                );
        }

        $builder->add(
            '__tab_info',
            TabType::class,
            [
                'label' => 'admin.user.tab.info.label',
                'inherit_data' => true,
            ]
        );
        // Add EAV data edition
        $this->buildDataForm($builder);

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                /** @var User $user */
                $user = $event->getData();
                if ($user) {
                    $user->setUpdatedAt(new \DateTime());
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'eavmanager_user';
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @throws \Exception
     */
    protected function buildDataForm(FormBuilderInterface $builder)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                /** @var User $user */
                $user = $event->getData();
                if (!$user->getId()) {
                    return;
                }

                $dataOptions = [
                    'label' => false,
                    'widget_form_group_attr' => false,
                    'horizontal_input_wrapper_class' => false,
                    'family' => 'User',
                ];

                if ($user && !$user->getData()) {
                    $dataOptions['data'] = $this->familyRegistry->getFamily('User')->createData();
                }
                /** @var FormBuilderInterface $infoTab */
                $infoTab = $form->get('__tab_info');
                $infoTab->add('data', DataType::class, $dataOptions);
            }
        );
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     */
    protected function getUser()
    {
        if (!$this->tokenStorage->getToken()) {
            return null;
        }

        return $this->tokenStorage->getToken()->getUser();
    }
}
