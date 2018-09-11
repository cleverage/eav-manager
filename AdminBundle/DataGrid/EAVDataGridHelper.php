<?php

namespace CleverAge\EAVManager\AdminBundle\DataGrid;

use Sidus\AdminBundle\Admin\Action;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\DataGridBundle\Registry\DataGridRegistry;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Clever Data Manager extension to base admin datagrid helper
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class EAVDataGridHelper
{
    /** @var DataGridHelper */
    protected $baseDataGridHelper;

    /** @var DataGridRegistry */
    protected $dataGridRegistry;

    /**
     * @param DataGridHelper   $baseDataGridHelper
     * @param DataGridRegistry $dataGridRegistry
     */
    public function __construct(DataGridHelper $baseDataGridHelper, DataGridRegistry $dataGridRegistry)
    {
        $this->baseDataGridHelper = $baseDataGridHelper;
        $this->dataGridRegistry = $dataGridRegistry;
    }

    /**
     * @param Action          $action
     * @param Request         $request
     * @param FamilyInterface $family
     * @param DataGrid|null   $dataGrid
     * @param array           $formOptions
     *
     * @return DataGrid
     */
    public function bindDataGridRequest(
        Action $action,
        Request $request,
        FamilyInterface $family,
        DataGrid $dataGrid = null,
        array $formOptions = []
    ): DataGrid {
        if (null === $dataGrid) {
            $dataGrid = $this->getDataGrid($action, $family);
        }

        return $this->baseDataGridHelper->bindDataGridRequest(
            $action,
            $request,
            $dataGrid,
            $formOptions
        );
    }

    /**
     * @param Request $request
     *
     * @return null|string
     */
    public function getTarget(Request $request): ?string
    {
        return $this->baseDataGridHelper->getTarget($request);
    }

    /**
     * @param Action          $action
     * @param FamilyInterface $family
     *
     * @return string
     */
    public function getDataGridConfigCode(Action $action, FamilyInterface $family): string
    {
        foreach ([$family->getCode(), strtolower($family->getCode())] as $dataGridCode) {
            if ($this->dataGridRegistry->hasDataGrid($dataGridCode)) {
                return $dataGridCode;
            }
        }

        return $this->baseDataGridHelper->getDataGridConfigCode($action);
    }

    /**
     * @param Action          $action
     * @param FamilyInterface $family
     *
     * @return DataGrid
     */
    public function getDataGrid(Action $action, FamilyInterface $family): DataGrid
    {
        return $this->dataGridRegistry->getDataGrid($this->getDataGridConfigCode($action, $family));
    }
}
