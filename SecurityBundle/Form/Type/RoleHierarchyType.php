<?php

namespace CleverAge\EAVManager\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CleverAge\EAVManager\SecurityBundle\Security\Core\Role\LeafRole;
use CleverAge\EAVManager\SecurityBundle\Security\Core\Role\RoleHierarchy;

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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hierarchy = $options['hierarchy'];
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($hierarchy) {
            $roles = $event->getData();
            $form = $event->getForm();

            if ($hierarchy instanceof LeafRole) {
                $options = [
                    'label' => $hierarchy->getRole(),
                    'required' => false,
                    'widget_checkbox_label' => 'widget',
                ];
                if (is_array($roles)) {
                    foreach ($roles as $role) {
                        if ($role === $hierarchy->getRole()) {
                            unset($roles[$role]);
                            $options['data'] = true;
                        }
                    }
                }
                $form->add('hasRole', 'checkbox', $options);
                $hierarchy = $hierarchy->getChildren();
            }
            foreach ($hierarchy as $subRole) {
                $form->add($subRole->getRole(), $this->getName(), [
                    'hierarchy' => $subRole,
                    'label' => false,
                    'data' => $roles,
                ]);
            }
        });
        $builder->addModelTransformer(new CallbackTransformer(
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
                        foreach ($items as $role) {
                            $submittedData[] = $role;
                        }
                    }
                }
                return $submittedData;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'hierarchy' => $this->roleHierarchy->getTreeHierarchy(),
            'required' => false,
        ]);
        $resolver->setNormalizer('hierarchy', function(Options $options, $value){
            $error = "'hierarchy' option must be a LeafRole or an array of LeafRole";
            if (!$value instanceof \Traversable && !$value instanceof LeafRole) {
                throw new \UnexpectedValueException($error);
            }
            if ($value instanceof \Traversable) {
                foreach ($value as $item) {
                    if (!$item instanceof LeafRole) {
                        throw new \UnexpectedValueException($error);
                    }
                }
            }
            return $value;
        });
    }


    public function getName()
    {
        return 'role_hierarchy';
    }
}
