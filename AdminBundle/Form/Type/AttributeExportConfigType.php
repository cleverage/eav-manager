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
