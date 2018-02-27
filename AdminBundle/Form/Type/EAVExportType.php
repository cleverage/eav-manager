<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

        /** @noinspection DateTimeConstantsUsageInspection */
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
