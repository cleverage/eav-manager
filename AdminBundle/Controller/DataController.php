<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\Component\Controller\DataControllerTrait;
use Elastica\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sidus\AdminBundle\Admin\Action;
use Sidus\EAVDataGridBundle\Model\DataGrid;
use Sidus\EAVFilterBundle\Configuration\ElasticaFilterConfigurationHandler;
use Sidus\EAVModelBundle\Entity\ContextualDataInterface;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_DATA_MANAGER')")
 */
class DataController extends BaseAdminController
{
    use DataControllerTrait;

    const SESSION_KEY = 'data_context';

    /** @var Form */
    protected $contextForm;

    /**
     * @Template()
     * @return array
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Security("is_granted('list', family) or is_granted('ROLE_SUPER_ADMIN')")
     * @Template()
     * @param FamilyInterface $family
     * @param Request         $request
     * @return array
     * @throws \Exception
     */
    public function listAction(FamilyInterface $family, Request $request)
    {
        $this->family = $family;
        $dataGrid = $this->getDataGrid();
        $dataGrid->setActionParameters('create', [
            'familyCode' => $family->getCode(),
        ]);
        $filterConfig = $dataGrid->getFilterConfig();

        if ($this->isElasticaEnabled() && $this->isElasticaUp()) {
            $finder = $this->container->get('fos_elastica.finder.sidus.data');
            if ($filterConfig instanceof ElasticaFilterConfigurationHandler) {
                $filterConfig->setFinder($finder);
                $filterConfig->getESQuery(); // trigger usage of elastic search
            }
        }

        $this->bindDataGridRequest($dataGrid, $request);

        return [
            'datagrid' => $dataGrid,
            'isAjax' => $request->isXmlHttpRequest(),
            'family' => $family,
            'target' => $this->getTarget($request),
        ];
    }

    /**
     * @Security("is_granted('create', family) or is_granted('ROLE_SUPER_ADMIN')")
     * @Template()
     * @param FamilyInterface $family
     * @param Request         $request
     * @return Response
     * @throws \Exception
     */
    public function createAction(FamilyInterface $family, Request $request)
    {
        /** @var DataInterface $data */
        $data = $family->createData();

        return $this->editAction($family, $data, $request);
    }

    /**
     * @Template()
     * @param FamilyInterface $family
     * @param DataInterface   $data
     * @param Request         $request
     * @return array|RedirectResponse
     * @throws \Exception
     */
    public function editAction(FamilyInterface $family, DataInterface $data, Request $request)
    {
        $this->initDataFamily($family, $data);
        $this->initContextForm($data, $request);

        $options = [];
        if (!$this->isGranted('edit', $family) && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            $options['disabled'] = true;
        }

        $form = $this->getForm($request, $data, $options);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->saveEntity($data);

            $parameters = [
                'success' => 1,
            ];
            if ($request->get('target')) {
                $parameters['target'] = $request->get('target');
            }

            return $this->redirectToEntity($data, 'edit', $parameters);
        }

        return $this->renderAction($this->getViewParameters($request, $form, $data));
    }

    /**
     * @Security("is_granted('delete', family) or is_granted('ROLE_SUPER_ADMIN')")
     * @Template()
     * @param FamilyInterface $family
     * @param DataInterface   $data
     * @param Request         $request
     * @return array|RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(FamilyInterface $family, DataInterface $data, Request $request)
    {
        $this->initDataFamily($family, $data);

        $builder = $this->createFormBuilder(null, $this->getDefaultFormOptions($request, $data->getId()));
        $form = $builder->getForm();
        $dataId = $data->getId();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->deleteEntity($data);

            if ($request->isXmlHttpRequest()) {
                return [
                    'family' => $family,
                    'dataId' => $dataId,
                    'isAjax' => 1,
                    'target' => $request->get('target'),
                    'success' => 1,
                ];
            }

            return $this->redirectToAdmin($this->admin, 'list', [
                'familyCode' => $family->getCode(),
            ]);
        }

        return $this->renderAction($this->getViewParameters($request, $form, $data) + [
            'dataId' => $dataId,
        ]);
    }

    protected function getDataGridConfigCode()
    {
        $code = strtolower($this->family->getCode());
        if ($this->get('sidus_data_grid.datagrid_configuration.handler')->hasDataGrid($code)) {
            return $code;
        }

        return 'data';
    }

    /**
     * @inheritdoc
     */
    protected function getDataGrid()
    {
        $datagrid = parent::getDataGrid();
        if ($datagrid instanceof DataGrid) {
            $datagrid->setFamily($this->family);
        }

        return $datagrid;
    }

    /**
     * @param ContextualDataInterface|DataInterface $data
     * @param Request                               $request
     * @throws \InvalidArgumentException
     */
    protected function initContextForm(DataInterface $data, Request $request)
    {
        if (!$data instanceof ContextualDataInterface || !$this->container->hasParameter('context_selector_type')) {
            return;
        }
        $contextSelectorType = $this->container->getParameter('context_selector_type');
        if ($request->getSession()->has(self::SESSION_KEY)) {
            $context = $request->getSession()->get(self::SESSION_KEY);
        } else {
            $context = $data->getFamily()->getDefaultContext();
        }
        $formOptions = [
            'action' => $this->getCurrentUri($request),
            'attr' => [
                'novalidate' => 'novalidate',
                'data-target' => $request->get('target'),
                'class' => 'form-inline',
            ],
        ];
        $this->contextForm = $this->createForm($contextSelectorType, $context, $formOptions);
        $this->contextForm->handleRequest($request);
        if ($this->contextForm->isValid()) {
            $context = $this->contextForm->getData();
            $request->getSession()->set(self::SESSION_KEY, $context);
            $request->getSession()->save();
        }
        $data->setCurrentContext($context);
    }

    /**
     * @param Request     $request
     * @param string      $dataId
     * @param Action|null $action
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getDefaultFormOptions(Request $request, $dataId, Action $action = null)
    {
        if (!$action) {
            $action = $this->admin->getCurrentAction();
        }
        $options = parent::getDefaultFormOptions($request, $dataId, $action);
        $options['label'] = "admin.family.{$this->family->getCode()}.{$action->getCode()}.title";

        return $options;
    }

    /**
     * @param Request       $request
     * @param Form          $form
     * @param DataInterface $data
     * @return array
     */
    protected function getViewParameters(Request $request, Form $form, $data)
    {
        return parent::getViewParameters($request, $form, $data) + [
            'family' => $data->getFamily(),
            'contextForm' => $this->contextForm ? $this->contextForm->createView() : null,
        ];
    }
}
