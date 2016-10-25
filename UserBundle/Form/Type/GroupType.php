<?php

namespace CleverAge\EAVManager\UserBundle\Form\Type;

use CleverAge\EAVManager\UserBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => 'admin.group.form.name.label',
            ])
            ->add('roles', 'role_hierarchy', [
                'label' => 'admin.group.form.roles.label',
            ])
            ->add('familyPermissions', 'sidus_bootstrap_collection', [
                'label' => 'admin.group.form.familyPermissions.label',
                'type' => 'family_permission',
                'options' => [],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'sortable' => false,
                'by_reference' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Group $group */
            $group = $event->getData();
            if ($group) {
                $group->setUpdatedAt(new \DateTime());
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
            'data_class' => Group::class,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'eavmanager_group';
    }
}
