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

use CleverAge\EAVManager\AdminBundle\DataGrid\AttributeDataGridHelper;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Admin\Admin;
use Sidus\AdminBundle\Configuration\AdminRegistry;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\DataGridBundle\Registry\DataGridRegistry;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Form\AllowedFamiliesOptionsConfigurator;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Overrides the template for the EmbedMultiFamilyCollectionType to display a datagrid instead
 */
class DataGridColumnType extends AbstractType
{
    /** @var AllowedFamiliesOptionsConfigurator */
    protected $allowedFamiliesOptionsConfigurator;

    /** @var DataGridRegistry */
    protected $dataGridRegistry;

    /** @var AttributeDataGridHelper */
    protected $attributeDataGridHelper;

    /** @var AdminRegistry */
    protected $adminRegistry;

    /**
     * @param AllowedFamiliesOptionsConfigurator $allowedFamiliesOptionsConfigurator
     * @param DataGridRegistry                   $dataGridRegistry
     * @param AttributeDataGridHelper            $attributeDataGridHelper
     * @param AdminRegistry                      $adminRegistry
     */
    public function __construct(
        AllowedFamiliesOptionsConfigurator $allowedFamiliesOptionsConfigurator,
        DataGridRegistry $dataGridRegistry,
        AttributeDataGridHelper $attributeDataGridHelper,
        AdminRegistry $adminRegistry
    ) {
        $this->allowedFamiliesOptionsConfigurator = $allowedFamiliesOptionsConfigurator;
        $this->dataGridRegistry = $dataGridRegistry;
        $this->attributeDataGridHelper = $attributeDataGridHelper;
        $this->adminRegistry = $adminRegistry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();

                /** @var DataGrid $dataGrid */
                $dataGrid = $options['datagrid'];
                /** @var AttributeInterface $attribute */
                $attribute = $options['attribute'];
                $this->attributeDataGridHelper->buildAttributeDataGrid($dataGrid, $data, $attribute);

                $form->add(
                    $form->getName(),
                    DataGridType::class,
                    [
                        'disabled' => $options['disabled'],
                        'datagrid' => $dataGrid,
                        'admin' => $options['admin'],
                        'admin_action' => $options['admin_action'],
                        'parent_data' => $data,
                        'parent_attribute' => $attribute,
                        'route_parameters' => $options['route_parameters_callback']($form, $data, $options),
                    ]
                );
            }
        );
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['admin'] = $options['admin'];
        $view->vars['allowed_families'] = $options['allowed_families'];
        $view->vars['target'] = $options['target'] ?: "tg_{$view->vars['id']}_modal";
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'inherit_data' => true,
                'attr' => [
                    'class' => 'mapping',
                ],
                'admin' => '_data',
                'admin_action' => 'attributeDataGrid',
                'target' => null,
                'route_parameters_callback' => function (FormInterface $form, DataInterface $data, array $options) {
                    return [
                        'id' => $data->getId(),
                        'attributeCode' => $options['attribute']->getCode(),
                        'dataGrid' => $options['datagrid']->getCode(),
                    ];
                },
            ]
        );
        $this->allowedFamiliesOptionsConfigurator->configureOptions($resolver);
        $resolver->setRequired(
            [
                'datagrid',
                'attribute',
            ]
        );
        $resolver->setAllowedTypes('datagrid', ['string', DataGrid::class]);
        $resolver->setAllowedTypes('admin', ['string', Admin::class]);
        $resolver->setAllowedTypes('attribute', [AttributeInterface::class]);
        $resolver->setNormalizer(
            'datagrid',
            function (/** @noinspection PhpUnusedParameterInspection */
                Options $options,
                $dataGrid
            ) {
                if (!$dataGrid instanceof DataGrid) {
                    $dataGrid = $this->dataGridRegistry->getDataGrid($dataGrid);
                }

                return $dataGrid;
            }
        );
        $resolver->setNormalizer(
            'admin_action',
            function (
                Options $options,
                $action
            ) {
                if ($action instanceof Action) {
                    return $action;
                }
                $admin = $options['admin'];
                if (null === $admin) {
                    throw new \UnexpectedValueException('Missing admin option when passing action as string');
                }
                if (!$admin instanceof Admin) {
                    $admin = $this->adminRegistry->getAdmin($admin);
                }

                return $admin->getAction($action);
            }
        );
    }
}
