<?php

namespace CleverAge\EAVManager\AdminBundle\DataGrid;

use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\DataGridBundle\Registry\DataGridRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Clever Data Manager extension to base admin datagrid helper
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DataGridHelper extends \Sidus\AdminBundle\DataGrid\DataGridHelper
{
    /** @var string */
    protected $defaultTarget;

    /**
     * @param DataGridRegistry     $dataGridRegistry
     * @param RoutingHelper        $routingHelper
     * @param FormFactoryInterface $formFactory
     * @param string               $method
     * @param string               $defaultTarget
     */
    public function __construct(
        DataGridRegistry $dataGridRegistry,
        RoutingHelper $routingHelper,
        FormFactoryInterface $formFactory,
        string $method = 'GET',
        string $defaultTarget = '#tg_center'
    ) {
        parent::__construct($dataGridRegistry, $routingHelper, $formFactory, $method);
        $this->defaultTarget = $defaultTarget;
    }

    /**
     * {@inheritdoc}
     */
    public function bindDataGridRequest(
        Action $action,
        Request $request,
        DataGrid $dataGrid = null,
        array $formOptions = []
    ): DataGrid {
        $target = $this->getTarget($request);
        if (null !== $target) {
            $formOptions['attr']['data-target-element'] = $target;
        }
        $formOptions['attr']['data-admin-code'] = $action->getAdmin()->getCode();

        return parent::bindDataGridRequest($action, $request, $dataGrid, $formOptions);
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    public function getTarget(Request $request): ?string
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->defaultTarget;
        }

        return $request->get('target', $this->defaultTarget);
    }
}
