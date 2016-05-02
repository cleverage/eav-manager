<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use Elastica\Query;
use Sidus\AdminBundle\Controller\AdminInjectable;
use Sidus\DataGridBundle\Model\DataGrid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CleverAge\EAVManager\Component\Controller\AdminControllerTrait;
use CleverAge\EAVManager\Component\Controller\BaseControllerTrait;

abstract class BaseAdminController extends Controller implements AdminInjectable
{
    use BaseControllerTrait;
    use AdminControllerTrait;

    /** @var string */
    protected $defaultTarget = 'tg_center';

    /**
     * @return string
     */
    protected function getDataGridConfigCode()
    {
        return $this->admin->getCode();
    }

    /**
     * @return DataGrid
     * @throws \UnexpectedValueException
     */
    protected function getDataGrid()
    {
        return $this->get('sidus_data_grid.datagrid_configuration.handler')
            ->getDataGrid($this->getDataGridConfigCode());
    }

    protected function getTarget(Request $request)
    {
        return $request->get('target', $this->defaultTarget);
    }

    protected function bindDataGridRequest(DataGrid $dataGrid, Request $request)
    {
        // Create form with filters
        $builder = $this->createFormBuilder(null, [
            'method' => $request->getMethod(),
            'csrf_protection' => false,
            'action' => $this->getCurrentUri($request),
            'attr' => [
                'data-target' => $this->getTarget($request),
            ],
        ]);
        $dataGrid->buildForm($builder);
        $dataGrid->handleRequest($request);
    }
}
