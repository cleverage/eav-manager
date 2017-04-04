<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\Component\Controller\EAVDataControllerTrait;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\EAVModelBundle\Entity\AbstractData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Admin\Action;
use Sidus\EAVDataGridBundle\Model\DataGrid as EAVDataGrid;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_DATA_MANAGER')")
 */
class EAVDataController extends AbstractAdminController
{
    use EAVDataControllerTrait;

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->admin->getOption('families', []) as $family => $options) {
            return $this->redirectToAction('list', ['familyCode' => $family]);
        }

        return $this->renderAction(
            [
                'admin' => $this->admin,
            ]
        );
    }

    /**
     * @Security("is_granted('list', family) or is_granted('ROLE_DATA_ADMIN')")
     *
     * @param FamilyInterface $family
     * @param Request         $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function listAction(FamilyInterface $family, Request $request)
    {
        $this->family = $family;
        $dataGrid = $this->getDataGrid();
        if ($dataGrid->hasAction('create')) {
            $dataGrid->setActionParameters(
                'create',
                [
                    'familyCode' => $family->getCode(),
                ]
            );
        }

        $this->bindDataGridRequest($dataGrid, $request);

        return $this->renderAction(
            array_merge(
                $this->getViewParameters($request),
                ['datagrid' => $dataGrid]
            )
        );
    }

    /**
     * @Security("is_granted('create', family) or is_granted('ROLE_DATA_ADMIN')")
     *
     * @param FamilyInterface $family
     * @param Request         $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function createAction(FamilyInterface $family, Request $request)
    {
        /** @var DataInterface $data */
        $data = $family->createData();

        return $this->editAction($family, $data, $request);
    }

    /**
     * Security check is done manually in the code : handles the read-only role
     *
     * @param FamilyInterface $family
     * @param DataInterface   $data
     * @param Request         $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function editAction(FamilyInterface $family, DataInterface $data, Request $request)
    {
        $this->initDataFamily($family, $data);

        $options = [];
        if ($this->admin->getCurrentAction() === 'edit' && !$this->isGranted('edit', $family)
            && !$this->isGranted('ROLE_DATA_ADMIN')
        ) {
            $options['disabled'] = true;
        }

        $form = $this->getForm($request, $data, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveEntity($data);

            $parameters = $request->query->all(); // @todo is this necessary ?
            $parameters['success'] = 1;

            return $this->redirectToEntity($data, 'edit', $parameters);
        }

        return $this->renderAction($this->getViewParameters($request, $form, $data));
    }

    /**
     * @Security("is_granted('delete', family) or is_granted('ROLE_DATA_ADMIN')")
     *
     * @param FamilyInterface $family
     * @param DataInterface   $data
     * @param Request         $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteAction(FamilyInterface $family, DataInterface $data, Request $request)
    {
        $this->initDataFamily($family, $data);

        $builder = $this->createFormBuilder(null, $this->getDefaultFormOptions($request, $data->getId()));
        $form = $builder->getForm();
        $dataId = $data->getId();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->deleteEntity($data);

            if ($request->isXmlHttpRequest()) {
                return $this->renderAction(
                    array_merge(
                        $this->getViewParameters($request, $form),
                        [
                            'dataId' => $dataId,
                            'success' => 1,
                        ]
                    )
                );
            }

            return $this->redirect($this->getAdminListPath());
        }

        return $this->renderAction(
            array_merge(
                $this->getViewParameters($request, $form, $data),
                [
                    'dataId' => $dataId,
                ]
            )
        );
    }

    /**
     * Resolve datagrid code
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    protected function getDataGridConfigCode()
    {
        if ($this->family) {
            // If datagrid code set in options, use it
            $familyCode = $this->family->getCode();
            /** @noinspection UnSafeIsSetOverArrayInspection */
            if (isset($this->admin->getOption('families')[$familyCode]['datagrid'])) {
                return $this->admin->getOption('families')[$familyCode]['datagrid'];
            }

            // Check if family has a datagrid with the same name
            if ($this->get('sidus_data_grid.datagrid_configuration.handler')->hasDataGrid($familyCode)) {
                return $familyCode;
            }
            // Check in lowercase (this should be deprecated ?)
            $code = strtolower($familyCode);
            if ($this->get('sidus_data_grid.datagrid_configuration.handler')->hasDataGrid($code)) {
                return $code;
            }
        }

        return parent::getDataGridConfigCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataGrid()
    {
        $datagrid = parent::getDataGrid();
        if ($datagrid instanceof EAVDataGrid) {
            $datagrid->setFamily($this->family);
        }

        return $datagrid;
    }

    /**
     * @param Request     $request
     * @param string      $dataId
     * @param Action|null $action
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getDefaultFormOptions(Request $request, $dataId, Action $action = null)
    {
        if (!$action) {
            $action = $this->admin->getCurrentAction();
        }
        $formOptions = parent::getDefaultFormOptions($request, $dataId, $action);
        $formOptions['label'] = $this->tryTranslate(
            [
                "admin.family.{$this->family->getCode()}.{$action->getCode()}.title",
                "admin.{$this->admin->getCode()}.{$action->getCode()}.title",
                "admin.action.{$action->getCode()}.title",
            ],
            [],
            ucfirst($action->getCode())
        );

        return $formOptions;
    }

    /**
     * @param Request       $request
     * @param Form          $form
     * @param DataInterface $data
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function getViewParameters(Request $request, Form $form = null, $data = null)
    {
        $parameters = parent::getViewParameters($request, $form, $data);
        if ($this->family) {
            $parameters['family'] = $this->family;
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdminListPath($data = null, array $parameters = [])
    {
        if ($this->family) {
            $parameters = array_merge(['familyCode' => $this->family->getCode()], $parameters);
        }

        return parent::getAdminListPath($data, $parameters);
    }

    /**
     * @param DataInterface $data
     *
     * @throws \Exception
     */
    protected function saveEntity($data)
    {
        if ($data instanceof AbstractData) {
            $data->setUpdatedAt(new \DateTime());
        }
        parent::saveEntity($data);
    }
}
