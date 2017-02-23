<?php

namespace CleverAge\EAVManager\SecurityBundle\Form\Type;

use CleverAge\EAVManager\SecurityBundle\Security\Core\Role\LeafRole;
use CleverAge\EAVManager\SecurityBundle\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Edit roles for users and groups
 */
class RoleHierarchyType extends AbstractType
{
    /** @var RoleHierarchy */
    protected $roleHierarchy;

    /**
     * @param RoleHierarchy $roleHierarchy
     */
    public function __construct(RoleHierarchy $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws UnexpectedTypeException
     * @throws \InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hierarchy = $options['hierarchy'];
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($hierarchy) {
                $roles = $event->getData();
                $form = $event->getForm();

                if ($hierarchy instanceof LeafRole) {
                    $options = [
                        'label' => $hierarchy->getRole(),
                        'required' => false,
                        'widget_checkbox_label' => 'widget',
                    ];
                    if (is_array($roles)) {
                        /** @var array $roles */
                        foreach ($roles as $role) {
                            if ($role === $hierarchy->getRole()) {
                                unset($roles[$role]);
                                $options['data'] = true;
                            }
                        }
                    }
                    $form->add('hasRole', CheckboxType::class, $options);
                    $hierarchy = $hierarchy->getChildren();
                }
                /** @var Role[] $hierarchy */
                foreach ($hierarchy as $subRole) {
                    $form->add(
                        $subRole->getRole(),
                        self::class,
                        [
                            'hierarchy' => $subRole,
                            'label' => false,
                            'data' => $roles,
                        ]
                    );
                }
            }
        );

        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($originalData) {
                    // Delete original data:
                    return null;
                },
                function ($submittedData) use ($hierarchy) {
                    if ($hierarchy instanceof LeafRole) {
                        if ($submittedData['hasRole']) {
                            $submittedData[] = $hierarchy->getRole();
                        }
                        unset($submittedData['hasRole']);
                    }
                    foreach ($submittedData as $key => $items) {
                        if (is_array($items)) {
                            unset($submittedData[$key]);
                            /** @var array $items */
                            foreach ($items as $role) {
                                $submittedData[] = $role;
                            }
                        }
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
     * @throws UndefinedOptionsException
     * @throws \UnexpectedValueException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'hierarchy' => $this->roleHierarchy->getTreeHierarchy(),
                'required' => false,
            ]
        );
        $resolver->setNormalizer(
            'hierarchy',
            function (Options $options, $value) {
                $error = "'hierarchy' option must be a LeafRole or an array of LeafRole";
                if (!$value instanceof \Traversable && !$value instanceof LeafRole) {
                    throw new \UnexpectedValueException($error);
                }
                if (is_array($value) || $value instanceof \Traversable) {
                    /** @var array $value */
                    foreach ($value as $item) {
                        if (!$item instanceof LeafRole) {
                            throw new \UnexpectedValueException($error);
                        }
                    }
                }

                return $value;
            }
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'role_hierarchy';
    }
}
