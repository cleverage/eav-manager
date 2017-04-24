<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\Component\Controller\EAVDataControllerTrait;
use CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository;
use CleverAge\EAVManager\ImportBundle\Model\CsvFile;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Entity\AbstractData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Admin\Action;
use Sidus\EAVDataGridBundle\Model\DataGrid as EAVDataGrid;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

        $this->bindDataGridRequest($dataGrid, $request);

        // Handle quick export directly in datagrid
        $form = $dataGrid->getForm();
        if ($form->isSubmitted() && $form->isValid()) {
            $button = $form->getClickedButton();
            if ($button && $button->getName() === 'export') {
                return $this->redirectToExport($dataGrid, $request->getSession());
            }
        }

        return $this->renderAction(
            array_merge(
                $this->getViewParameters($request),
                ['datagrid' => $dataGrid]
            )
        );
    }

    /**
     * @Security("is_granted('list', family) or is_granted('ROLE_DATA_ADMIN')")
     *
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function exportAction(Request $request, FamilyInterface $family)
    {
        $this->family = $family;

        $exportConfig = $this->getExportConfig($request);
        $defaultFormOptions = $this->getDefaultFormOptions($request, 'export');
        $attr = isset($defaultFormOptions['attr']) ? $defaultFormOptions['attr'] : [];
        $attr['data-target-element'] = null; // Deep merge hell
        $form = $this->getForm(
            $request,
            $exportConfig,
            [
                'family' => $family,
                'attr' => $attr,
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->generateExport($form->getData());
        }

        return $this->renderAction($this->getViewParameters($request, $form));
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

        $formOptions = $this->getDefaultFormOptions($request, $data->getId());
        unset($formOptions['family']);
        $builder = $this->createFormBuilder(null, $formOptions);
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
            if ($datagrid->hasAction('create')) {
                $datagrid->setActionParameters(
                    'create',
                    [
                        'familyCode' => $this->family->getCode(),
                    ]
                );
            }
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
        $formOptions['family'] = $this->family;
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

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getExportConfig(Request $request)
    {
        $configKey = $request->get('configKey');
        if (!$configKey) {
            return [];
        }
        $session = $request->getSession();
        $selectedIds = $session->get('export_selected_ids_'.$configKey);
        $selectedColumns = $session->get('export_selected_columns_'.$configKey);
        $attributes = [];
        if (is_array($selectedColumns)) {
            /** @var array $selectedColumns */
            foreach ($selectedColumns as $selectedColumn) {
                $attributes[$selectedColumn] = [
                    'enabled' => true,
                ];
            }
        }

        return [
            'selectedIds' => is_array($selectedIds) ? implode('|', $selectedIds) : null,
            'onlySelectedEntities' => (bool) $selectedIds,
            'attributes' => $attributes,
        ];
    }

    /**
     * @param DataGrid         $dataGrid
     * @param SessionInterface $session
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToExport(DataGrid $dataGrid, SessionInterface $session)
    {
        $alias = $dataGrid->getFilterConfig()->getAlias();
        $qb = $dataGrid->getFilterConfig()->getQueryBuilder();
        $qb->select($alias.'.id');

        $selectedIds = [];
        foreach ($qb->getQuery()->getArrayResult() as $result) {
            $selectedIds[] = $result['id'];
        }

        $selectedColumns = [];
        foreach ($dataGrid->getColumns() as $column) {
            $selectedColumns[] = $column->getPropertyPath();
        }

        $configKey = uniqid('', false);
        $session->set('export_selected_ids_'.$configKey, $selectedIds);
        $session->set('export_selected_columns_'.$configKey, $selectedColumns);

        return $this->redirectToAction(
            'export',
            [
                'familyCode' => $this->family->getCode(),
                'configKey' => $configKey,
            ]
        );
    }

    /**
     * @param array $config
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return StreamedResponse
     */
    public function generateExport(array $config)
    {
        $response = new StreamedResponse();
        $response->setCallback(
            function () use ($config) {
                $headers = [];
                /** @var array $attributesConfig */
                $attributesConfig = $config['attributes'];
                foreach ($attributesConfig as $attributeCode => $attributeConfig) {
                    if ($attributeConfig['enabled']) {
                        $headers[] = $attributeConfig['column'];
                    }
                }

                $csvFile = new CsvFile(
                    'php://output',
                    $config['csvDelimiter'],
                    $config['csvEnclosure'],
                    $config['csvEscape'],
                    $headers,
                    'w'
                );
                $csvFile->writeLine(array_combine($headers, $headers));

                $doctrine = $this->getDoctrine();
                /** @var DataRepository $repository */
                $repository = $doctrine->getRepository($this->family->getDataClass());
                $qb = $repository->createQueryBuilder('e');
                $qb
                    ->andWhere('e.family = :family')
                    ->setParameter('family', $this->family->getCode())
                ;
                if ($config['onlySelectedEntities']) {
                    $selectedIds = explode('|', $config['selectedIds']);
                    $identifierAttribute = $this->family->getAttributeAsIdentifier();
                    if ($identifierAttribute) {
                        $eavQb = new EAVQueryBuilder($qb, 'e');
                        $eavQb->attribute($identifierAttribute)->in($selectedIds);
                    } else {
                        $qb
                            ->andWhere('e.id IN (:selectedIds)')
                            ->setParameter('selectedIds', $selectedIds);
                    }
                }

                /** @var NormalizerInterface $normalizer */
                $normalizer = $this->get('serializer');
                foreach ($qb->getQuery()->iterate() as $row) {
                    $entity = $row[0];
                    $normalizedData = $normalizer->normalize($entity, 'csv');
                    $writableData = [];
                    foreach ($attributesConfig as $attributeCode => $attributeConfig) {
                        if (!$attributeConfig['enabled']) {
                            continue;
                        }
                        $attribute = $this->family->getAttribute($attributeCode);
                        $value = $normalizedData[$attributeCode];

                        $serializedColumn = null;
                        if (isset($attributeConfig['serializedColumn'])) {
                            $serializedColumn = $attributeConfig['serializedColumn'];
                        }

                        if ($attribute->isCollection() && is_array($value)) {
                            $values = [];
                            foreach ($value as $item) {
                                $values[] = $this->normalizeRelation($entity, $serializedColumn, $item);
                            }
                            $value = implode($config['splitCharacter'], $values);
                        } else {
                            $value = $this->normalizeRelation($entity, $serializedColumn, $value);
                        }
                        $writableData[$attributeConfig['column']] = $value;
                    }

                    $csvFile->writeLine($writableData);

                    /** @noinspection DisconnectedForeachInstructionInspection */
                    $doctrine->getManager()->clear();
                }

                $csvFile->close();
            }
        );

        $date = date('Y-m-d');
        $filename = "{$this->family->getCode()}_{$date}.csv";
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    /**
     * @param DataInterface $entity
     * @param string        $serializedColumn
     * @param mixed         $value
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    protected function normalizeRelation(DataInterface $entity, $serializedColumn, $value)
    {
        if (!$serializedColumn || !is_array($value)) {
            return $value;
        }

        if (array_key_exists($serializedColumn, $value)) {
            return $value[$serializedColumn];
        } elseif ($value !== null) {
            throw new \UnexpectedValueException(
                "Unknown serialized format for entity #{$entity->getId()}"
            );
        }

        return $value;
    }
}
