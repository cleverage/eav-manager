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

namespace CleverAge\EAVManager\AdminBundle\Form\Type;

use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Export configuration builder.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class EAVExportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var FamilyInterface $family */
        $family = $options['family'];

        $builder
            ->add(
                'format',
                ChoiceType::class,
                [
                    'choices' => [
                        'CSV' => 'csv',
                        'JSON' => 'json',
                    ],
                ]
            )
            ->add(
                'csvDelimiter',
                ChoiceType::class,
                [
                    'choices' => [
                        'Semicolon ;' => ';',
                        'Comma ,' => ',',
                        'Tab \\t' => "\t",
                        'Pipe |' => '|',
                    ],
                ]
            )
            ->add(
                'csvEnclosure',
                ChoiceType::class,
                [
                    'choices' => [
                        '"' => '"',
                        "'" => "'",
                    ],
                ]
            )
            ->add(
                'csvEscape',
                ChoiceType::class,
                [
                    'choices' => [
                        'Backslash \\' => '\\',
                    ],
                ]
            )
            ->add(
                'onlySelectedEntities',
                CheckboxType::class,
                [
                    'required' => false,
                    'help_block' => 'If selected, only the entities with the following Ids will be exported',
                    'widget_checkbox_label' => 'label',
                ]
            )
            ->add(
                'selectedIds',
                TextType::class,
                [
                    'attr' => [
                        'readonly' => 'readonly',
                    ],
                    'required' => false,
                    'help_block' => 'These values comes from the last resultset found in session',
                ]
            )
            ->add(
                'dateFormat',
                ChoiceType::class,
                [
                    'choices' => [
                        'ISO 8601' => \DateTime::ISO8601,
                        'W3C/ATOM (RFC3339)' => \DateTime::W3C,
                        'Simple date: Y-m-d' => 'Y-m-d',
                        'Simple datetime: Y-m-d H:i' => 'Y-m-d H:i',
                    ],
                ]
            )
            ->add(
                'splitCharacter',
                ChoiceType::class,
                [
                    'choices' => [
                        'Comma ,' => ',',
                        'Pipe |' => '|',
                        'Semicolon ;' => ';',
                        'Slash /' => '/',
                    ],
                    'help_block' => 'For collection attributes',
                ]
            )
            ->add(
                'booleanFormat',
                ChoiceType::class,
                [
                    'choices' => [
                        '1/0' => 'binary',
                        'true/false' => 'boolean',
                        'yes/no' => 'english',
                    ],
                    'help_block' => 'For boolean attributes',
                ]
            )
            ->add(
                'attributes',
                FormType::class,
                [
                    'required' => false,
                    'help_label' => '<div class="btn-group"><button type="button" data-select-all="form_project_export[attributes]" class="btn btn-default">Select all</button><button type="button" data-select-none="form_project_export[attributes]" class="btn btn-default">Select none</button></div>',
                ]
            );

        /** @var FormBuilderInterface $attributesBuilder */
        $attributesBuilder = $builder->get('attributes');
        foreach ($family->getAttributes() as $attribute) {
            $attributesBuilder->add(
                $attribute->getCode(),
                AttributeExportConfigType::class,
                [
                    'required' => false,
                    'attribute' => $attribute,
                ]
            );
        }
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'family',
            ]
        );
        $resolver->setAllowedTypes('family', FamilyInterface::class);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'cleverage_eav_export';
    }
}
