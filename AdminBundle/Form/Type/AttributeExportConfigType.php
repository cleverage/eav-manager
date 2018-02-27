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

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Export configuration builder.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class AttributeExportConfigType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AttributeInterface $attribute */
        $attribute = $options['attribute'];

        $builder
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'widget_checkbox_label' => 'label',
                ]
            )
            ->add(
                'column',
                TextType::class,
                [
                    'data' => $attribute->getCode(),
                ]
            );

        if ($attribute->getType()->isRelation()) {
            $builder->add(
                'serializedColumn',
                ChoiceType::class,
                [
                    'choices' => [
                        'EAV Identifier' => 'identifier',
                        'Doctrine Id' => 'id',
                        'Label (WARNING: not re-importable)' => 'label',
                    ],
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
        $resolver->setDefaults(
            [
                'help_label' => null,
            ]
        );
        $resolver->setRequired(
            [
                'attribute',
            ]
        );
        $resolver->setAllowedTypes('attribute', AttributeInterface::class);

        $resolver->setNormalizer(
            'help_label',
            function (Options $options, $value) {
                if (null !== $value) {
                    return $value;
                }
                /** @var AttributeInterface $attribute */
                $attribute = $options['attribute'];

                $messages = [];
                if ($attribute->getType()->isRelation()) {
                    $messages[] = '<p class="text-warning"><b>Warning:</b> This attribute is a relation, in a flat format (like CSV) only the configured column will be exported</p>';
                }
                if ($attribute->isCollection()) {
                    $messages[] = '<p class="text-warning"><b>Warning:</b> This attribute is a collection, in a flat format (like CSV) the values will be joined together using the split character</p>';
                }
                if ($attribute->isUnique()) {
                    return '<p class="text-muted">This attribute is unique</p>';
                }

                return implode("\n", $messages);
            }
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'cleverage_attribute_export_config';
    }
}
