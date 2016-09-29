<?php

namespace CleverAge\EAVManager\UserBundle\Form\Type;

use CleverAge\EAVManager\SecurityBundle\Form\Type\FamilyPermissionType;
use CleverAge\EAVManager\SecurityBundle\Form\Type\RoleHierarchyType;
use CleverAge\EAVManager\UserBundle\Entity\Group;
use CleverAge\EAVManager\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Sidus\EAVBootstrapBundle\Form\Type\BootstrapCollectionType;
use Sidus\EAVBootstrapBundle\Form\Type\SwitchType;
use Sidus\EAVModelBundle\Form\Type\DataType;
use Sidus\EAVModelBundle\Model\Family;
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

class UserType extends AbstractType
{
    /** @var Family */
    protected $family;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param Family                        $family
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        Family $family,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->family = $family;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('__tab_main', TabType::class, [
            'label' => 'admin.user.tab.main.label',
            'inherit_data' => true,
        ]);
        $builder->get('__tab_main')
            ->add('username', TextType::class, [
                'label' => 'admin.user.form.username.label',
            ])
            ->add('email', EmailType::class, [
                'label' => 'admin.user.form.email.label',
            ]);

        if ($this->getUser() && $this->authorizationChecker->isGranted('ROLE_ADMIN', $this->getUser())) {
            $builder->get('__tab_main')
                ->add('enabled', SwitchType::class, [
                    'label' => 'admin.user.form.enabled.label',
                ])
                ->add('rawRoles', RoleHierarchyType::class, [
                    'label' => 'admin.user.form.roles.label',
                ])
                ->add('familyPermissions', BootstrapCollectionType::class, [
                    'label' => 'admin.user.form.familyPermissions.label',
                    'type' => FamilyPermissionType::class,
                    'options' => [],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'sortable' => false,
                    'by_reference' => false,
                ]);

            $builder->add('__tab_groups', TabType::class, [
                'label' => 'admin.user.tab.groups.label',
                'inherit_data' => true,
            ]);
            $builder->get('__tab_groups')
                ->add('groups', EntityType::class, [
                    'label' => 'admin.user.form.groups.label',
                    'class' => Group::class,
                    'expanded' => true,
                    'multiple' => true,
                ]);
        }

        $builder->add('__tab_info', TabType::class, [
            'label' => 'admin.user.tab.info.label',
            'inherit_data' => true,
        ]);
        // Add EAV data edition
        $this->buildDataForm($builder);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var User $user */
            $user = $event->getData();
            if ($user) {
                $user->setUpdatedAt(new \DateTime());
            }
        });
    }

    /**
     * {@inheritDoc}
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
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
     * @throws \Exception
     */
    protected function buildDataForm(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
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
            ];

            if ($user && !$user->getData()) {
                $dataOptions['data'] = $this->family->createData();
            }
            $form->get('__tab_info')->add('data', DataType::class, $dataOptions);
        });
    }

    /**
     * @return UserInterface|null
     */
    protected function getUser()
    {
        if (!$this->tokenStorage->getToken()) {
            return null;
        }

        return $this->tokenStorage->getToken()->getUser();
    }
}
