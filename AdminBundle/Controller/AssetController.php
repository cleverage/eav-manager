<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\AssetBundle\Entity\Document;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sidus\AdminBundle\Routing\AdminRouter;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Used to browse assets inside media selectors
 */
class AssetController extends DataController
{
    /** @var bool */
    protected $browserMode = false;

    /**
     * @Template()
     * @param FamilyInterface $family
     * @param Request         $request
     * @param string          $inputId
     *
     * @return array
     * @throws \Exception
     */
    public function browseAction(FamilyInterface $family, Request $request, $inputId)
    {
        $this->browserMode = true;
        $this->family = $family;
        $dataGrid = $this->getBrowserDataGrid($inputId);

        $this->bindDataGridRequest($dataGrid, $request);

        return [
            'datagrid' => $dataGrid,
            'isAjax' => $request->isXmlHttpRequest(),
            'family' => $family,
            'target' => $this->getTarget($request),
            'inputId' => $inputId,
            'admin' => $this->admin,
        ];
    }

    /**
     * @Template()
     * @param FamilyInterface $family
     * @param Request         $request
     * @param string          $inputId
     *
     * @return array
     * @throws \Exception
     */
    public function browseThumbnailAction(FamilyInterface $family, Request $request, $inputId)
    {
        $this->browserMode = true;
        $this->family = $family;
        $dataGrid = $this->getBrowserDataGrid($inputId);

        $this->bindDataGridRequest($dataGrid, $request);

        return [
            'datagrid' => $dataGrid,
            'isAjax' => $request->isXmlHttpRequest(),
            'family' => $family,
            'target' => $this->getTarget($request),
            'inputId' => $inputId,
            'admin' => $this->admin,
        ];
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    protected function getDataGridConfigCode()
    {
        if ($this->admin->getCurrentAction()->getCode() === 'browseThumbnail') {
            return 'thumbnail_browser';
        }

        $code = parent::getDataGridConfigCode();

        return $code.($this->browserMode ? '_browser' : '');
    }

    /**
     * @param string $inputId
     *
     * @return DataGrid
     * @throws \UnexpectedValueException
     */
    protected function getBrowserDataGrid($inputId)
    {
        $dataGrid = parent::getDataGrid();

        if ($dataGrid->hasAction('create')) {
            $dataGrid->setActionParameters(
                'create',
                [
                    'familyCode' => $this->family->getCode(),
                    'inputId' => $inputId,
                ]
            );
        }
        if ($dataGrid->hasAction('browseThumbnail')) {
            $dataGrid->setActionParameters(
                'browseThumbnail',
                [
                    'familyCode' => $this->family->getCode(),
                    'inputId' => $inputId,
                ]
            );
        }
        if ($dataGrid->hasAction('browse')) {
            $dataGrid->setActionParameters(
                'browse',
                [
                    'familyCode' => $this->family->getCode(),
                    'inputId' => $inputId,
                ]
            );
        }

        return $dataGrid;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdminListPath($data = null, array $parameters = [])
    {
        if (!$this->browserMode) {
            return parent::getAdminListPath($data, $parameters);
        }
        /** @var AdminRouter $adminRouter */
        $adminRouter = $this->get('sidus_admin.routing.admin_router');

        return $adminRouter->generateAdminPath(
            $this->admin,
            'browse',
            array_merge(['familyCode' => $this->family->getCode()], $parameters)
        );
    }
}
