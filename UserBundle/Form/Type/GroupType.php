<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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

/**
 * User group type.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'admin.group.form.name.label',
                ]
            )
            ->add(
                'roles',
                RoleHierarchyType::class,
                [
                    'label' => 'admin.group.form.roles.label',
                ]
            )
            ->add(
                'familyPermissions',
                BootstrapCollectionType::class,
                [
                    'label' => 'admin.group.form.familyPermissions.label',
                    'entry_type' => FamilyPermissionType::class,
                    'entry_options' => [],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'sortable' => false,
                    'by_reference' => false,
                ]
            );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                /** @var Group $group */
                $group = $event->getData();
                if ($group) {
                    $group->setUpdatedAt(new \DateTime());
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
                'data_class' => Group::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'eavmanager_group';
    }
}
