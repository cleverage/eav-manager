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

use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Admin\Admin;
use Sidus\AdminBundle\Configuration\AdminRegistry;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\DataGridBundle\Registry\DataGridRegistry;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Render a datagrid inside a form
 */
class DataGridType extends AbstractType
{
    /** @var DataGridRegistry */
    protected $dataGridRegistry;

    /** @var AdminRegistry */
    protected $adminRegistry;

    /**
     * @param DataGridRegistry $dataGridRegistry
     * @param AdminRegistry    $adminRegistry
     */
    public function __construct(DataGridRegistry $dataGridRegistry, AdminRegistry $adminRegistry)
    {
        $this->dataGridRegistry = $dataGridRegistry;
        $this->adminRegistry = $adminRegistry;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['datagrid'] = $options['datagrid'];
        $view->vars['datagrid_vars'] = array_merge(
            $options['datagrid_vars'],
            [
                'parent_data' => $options['parent_data'],
                'parent_attribute' => $options['parent_attribute'],
                'action' => $options['action'],
                'route_parameters' => $options['route_parameters'],
            ]
        );
        $view->vars['admin'] = $options['admin'];
        $view->vars['action'] = $options['action'];
        $view->vars['route_parameters'] = $options['route_parameters'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var DataGrid $dataGrid */
        $dataGrid = $options['datagrid'];
        $dataGrid->buildForm($builder->create('filter', FormType::class));
        if (null !== $options['request_data']) {
            $dataGrid->handleArray($options['request_data']);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'parent_data',
                'parent_attribute',
                'datagrid',
                'action',
            ]
        );
        $resolver->setDefaults(
            [
                'request_data' => null,
                'datagrid_vars' => [],
                'admin' => null,
                'route_parameters' => [],
            ]
        );
        $resolver->setAllowedTypes('parent_data', [DataInterface::class]);
        $resolver->setAllowedTypes('parent_attribute', [AttributeInterface::class]);
        $resolver->setAllowedTypes('datagrid', ['string', DataGrid::class]);
        $resolver->setAllowedTypes('request_data', ['NULL', 'array']);
        $resolver->setAllowedTypes('admin', ['NULL', 'string', Admin::class]);
        $resolver->setAllowedTypes('action', ['string', Action::class]);
        $resolver->setAllowedTypes('route_parameters', ['array']);
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
            'action',
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
