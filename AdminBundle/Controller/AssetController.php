<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AssetController extends DataController
{
    /** @var bool */
    protected $browserMode = false;

    /**
     * @Template()
     * @param FamilyInterface $family
     * @param Request         $request
     * @param string          $inputId
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
        ];
    }

    /**
     * @Template()
     * @param FamilyInterface $family
     * @param Request         $request
     * @param string          $inputId
     * @return array
     * @throws \Exception
     */
    public function browseThumbnailAction(FamilyInterface $family, Request $request, $inputId)
    {
        $this->browserMode = true;
        $this->family = $family;
        $dataGrid = $this->getDataGrid();
        $dataGrid->setActionParameters('create', [
            'familyCode' => $family->getCode(),
            'inputId' => $inputId,
        ]);
        $dataGrid->setActionParameters('browse', [
            'familyCode' => $family->getCode(),
            'inputId' => $inputId,
        ]);

        $this->bindDataGridRequest($dataGrid, $request);

        return [
            'datagrid' => $dataGrid,
            'isAjax' => $request->isXmlHttpRequest(),
            'family' => $family,
            'target' => $this->getTarget($request),
            'inputId' => $inputId,
        ];
    }

    /**
     * @Template()
     * @param FamilyInterface $family
     * @param Request         $request
     * @param string          $inputId
     * @return Response
     * @throws \Exception
     */
    public function createModalAction(FamilyInterface $family, Request $request, $inputId)
    {
        /** @var DataInterface $data */
        $data = $family->createData();

        return $this->editModalAction($family, $data, $request, $inputId);
    }

    /**
     * @Security("is_granted('edit', family) or is_granted('ROLE_SUPER_ADMIN')")
     * @Template()
     * @param FamilyInterface $family
     * @param DataInterface   $data
     * @param Request         $request
     * @param string          $inputId
     * @return array|RedirectResponse
     * @throws \Exception
     */
    public function editModalAction(FamilyInterface $family, DataInterface $data, Request $request, $inputId)
    {
        $this->initDataFamily($family, $data);

        $form = $this->getForm($request, $data);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->saveEntity($data);

            $parameters = [
                'familyCode' => $family->getCode(),
                'inputId' => $inputId,
                'success' => 1,
            ];
            if ($request->get('target')) {
                $parameters['target'] = $request->get('target');
            }

            return $this->redirectToAdmin($this->admin->getCode(), 'browse', $parameters);
        }

        return $this->renderAction($this->getViewParameters($request, $form, $data) + [
                'inputId' => $inputId,
            ]);
    }

    /**
     * @return string
     */
    protected function getDataGridConfigCode()
    {
        if ($this->admin->getCurrentAction()->getCode() === 'browseThumbnail') {
            return 'thumbnail_browser';
        }

        return strtolower($this->family->getCode()).($this->browserMode ? '_browser' : '');
    }

    /**
     * @param string $inputId
     * @return DataGrid
     * @throws \UnexpectedValueException
     */
    protected function getBrowserDataGrid($inputId)
    {
        $dataGrid = parent::getDataGrid();

        if ($dataGrid->hasAction('create')) {
            $dataGrid->setActionParameters('create', [
                'familyCode' => $this->family->getCode(),
                'inputId' => $inputId,
            ]);
        }
        if ($dataGrid->hasAction('browseThumbnail')) {
            $dataGrid->setActionParameters('browseThumbnail', [
                'familyCode' => $this->family->getCode(),
                'inputId' => $inputId,
            ]);
        }
        if ($dataGrid->hasAction('browse')) {
            $dataGrid->setActionParameters('browse', [
                'familyCode' => $this->family->getCode(),
                'inputId' => $inputId,
            ]);
        }

        return $dataGrid;
    }
}
