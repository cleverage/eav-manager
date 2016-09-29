<?php

namespace CleverAge\EAVManager\UserBundle\Form\Type;

use CleverAge\EAVManager\SecurityBundle\Form\Type\FamilyPermissionType;
use CleverAge\EAVManager\SecurityBundle\Form\Type\RoleHierarchyType;
use CleverAge\EAVManager\UserBundle\Entity\Group;
use Sidus\EAVBootstrapBundle\Form\Type\BootstrapCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('name', TextType::class, [
                'label' => 'admin.group.form.name.label',
            ])
            ->add('roles', RoleHierarchyType::class, [
                'label' => 'admin.group.form.roles.label',
            ])
            ->add('familyPermissions', BootstrapCollectionType::class, [
                'label' => 'admin.group.form.familyPermissions.label',
                'type' => FamilyPermissionType::class,
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
    public function getBlockPrefix()
    {
        return 'eavmanager_group';
    }
}
