<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\Component\Controller\EAVDataControllerTrait;
use CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository;
use CleverAge\ProcessBundle\Filesystem\CsvFile;
use Doctrine\ORM\EntityManagerInterface;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Entity\AbstractData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Admin\Action;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Query\Handler\Doctrine\DoctrineQueryHandlerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Sidus\DataGridBundle\Registry\DataGridRegistry;
use Sidus\EAVModelBundle\Doctrine\IntegrityConstraintManager;

/**
 * Specific controller for EAV Data
 *
 * @Security("is_granted('ROLE_DATA_MANAGER')")
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
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
    public function indexAction(/** @noinspection PhpUnusedParameterInspection */
        Request $request
    ) {
        /** @var array $families */
        $families = $this->admin->getOption('families', []);
        /* @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($families as $family => $options) {
            return $this->redirectToAction('list', ['familyCode' => $family]);
        }

        return $this->renderAction(
            [
                'admin' => $this->admin,
            ]
        );
    }

    /**
     * @Security("is_granted('list', family)")
     *
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function listAction(Request $request, FamilyInterface $family)
    {
        $this->setFamily($family);
        $dataGrid = $this->getDataGrid();

        $this->bindDataGridRequest($dataGrid, $request);

        // Handle quick export directly in datagrid
        $form = $dataGrid->getForm();
        if ($form->isSubmitted() && $form->isValid()) {
            $button = $form->getClickedButton();
            if ($button && 'export' === $button->getName()) {
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
     * @Security("is_granted('list', family)")
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
        $this->setFamily($family);

        $exportConfig = $this->getExportConfig($request);
        $defaultFormOptions = $this->getDefaultFormOptions($request, 'export');
        $attr = $defaultFormOptions['attr'] ?? [];
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
     * @Security("is_granted('create', family)")
     *
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function createAction(Request $request, FamilyInterface $family)
    {
        /** @var DataInterface $data */
        $data = $family->createData();

        return $this->editAction($request, $data, $family);
    }

    /**
     * @Security("is_granted('read', data)")
     *
     * @param Request         $request
     * @param DataInterface   $data
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function readAction(Request $request, DataInterface $data, FamilyInterface $family = null)
    {
        $this->initDataFamily($data, $family);
        $form = $this->getForm($request, $data, ['disabled' => true]);

        return $this->renderAction($this->getViewParameters($request, $form, $data));
    }

    /**
     * @Security("is_granted('edit', data)")
     *
     * @param Request         $request
     * @param DataInterface   $data
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function editAction(Request $request, DataInterface $data, FamilyInterface $family = null)
    {
        $this->initDataFamily($data, $family);
        $form = $this->getForm($request, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveEntity($data);

            $parameters = $request->query->all();
            $parameters['success'] = 1;

            return $this->redirectToEntity($data, 'edit', $parameters);
        }

        return $this->renderAction($this->getViewParameters($request, $form, $data));
    }

    /**
     * Dedicated permission for cloning ?
     *
     * @Security("(is_granted('create', family) and is_granted('read', data))")
     *
     * @param Request         $request
     * @param DataInterface   $data
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function cloneAction(Request $request, DataInterface $data, FamilyInterface $family = null)
    {
        return $this->editAction($request, clone $data, $family);
    }

    /**
     * @Security("is_granted('delete', data)")
     *
     * @param Request         $request
     * @param DataInterface   $data
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteAction(Request $request, DataInterface $data, FamilyInterface $family = null)
    {
        $this->initDataFamily($data, $family);
        $constrainedEntities = $this->get(IntegrityConstraintManager::class)->getEntityConstraints($data);

        $formOptions = $this->getDefaultFormOptions($request, $data->getId());
        unset($formOptions['family']);
        $builder = $this->createFormBuilder(null, $formOptions);
        $form = $builder->getForm();
        $dataId = $data->getId();

        if (0 === \count($constrainedEntities)) {
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
        }

        return $this->renderAction(
            array_merge(
                $this->getViewParameters($request, $form, $data),
                [
                    'dataId' => $dataId,
                    'constrainedEntities' => $constrainedEntities,
                ]
            )
        );
    }

    /**
     * Resolve datagrid code.
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    protected function getDataGridConfigCode(): string
    {
        if ($this->family) {
            // If datagrid code set in options, use it
            $familyCode = $this->family->getCode();
            /* @noinspection UnSafeIsSetOverArrayInspection */
            if (isset($this->admin->getOption('families')[$familyCode]['datagrid'])) {
                return $this->admin->getOption('families')[$familyCode]['datagrid'];
            }

            // Check if family has a datagrid with the same name
            if ($this->get(DataGridRegistry::class)->hasDataGrid($familyCode)) {
                return $familyCode;
            }
            // Check in lowercase (this should be deprecated ?)
            $code = strtolower($familyCode);
            if ($this->get(DataGridRegistry::class)->hasDataGrid($code)) {
                return $code;
            }
        }

        return parent::getDataGridConfigCode();
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
    protected function getDefaultFormOptions(Request $request, $dataId, Action $action = null): array
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
    protected function getViewParameters(Request $request, Form $form = null, $data = null): array
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
    protected function getAdminListPath($data = null, array $parameters = []): string
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
        $selectedIds = null;
        $selectedColumns = null;
        if ($session) {
            $selectedIds = $session->get('export_selected_ids_'.$configKey);
            $selectedColumns = $session->get('export_selected_columns_'.$configKey);
        }
        $attributes = [];
        if (\is_array($selectedColumns)) {
            /** @var array $selectedColumns */
            foreach ($selectedColumns as $selectedColumn) {
                $attributes[$selectedColumn] = [
                    'enabled' => true,
                ];
            }
        }

        return [
            'selectedIds' => \is_array($selectedIds) ? implode('|', $selectedIds) : null,
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
        $filterConfig = $dataGrid->getQueryHandler();
        $selectedIds = [];
        if ($filterConfig instanceof DoctrineQueryHandlerInterface) {
            $alias = $filterConfig->getAlias();
            $qb = $filterConfig->getQueryBuilder();
            $qb->select($alias.'.id');

            $selectedIds = [];
            foreach ($qb->getQuery()->getArrayResult() as $result) {
                $selectedIds[] = $result['id'];
            }
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
     * @throws \Symfony\Component\Serializer\Exception\LogicException
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\CircularReferenceException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     *
     * @return StreamedResponse
     */
    protected function generateExport(array $config)
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

                $manager = $this->getDoctrine()->getManagerForClass($this->family->getDataClass());
                if (!$manager instanceof EntityManagerInterface) {
                    throw new \UnexpectedValueException("No manager found for class {$this->family->getDataClass()}");
                }
                /** @var DataRepository $repository */
                $repository = $manager->getRepository($this->family->getDataClass());
                $qb = $repository->createQueryBuilder('e');
                $qb
                    ->andWhere('e.family = :family')
                    ->setParameter('family', $this->family->getCode());
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
                    /** @var array $normalizedData */
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

                        if (\is_array($value) && $attribute->isCollection()) {
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

                    /* @noinspection DisconnectedForeachInstructionInspection */
                    $manager->clear();
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
        if (!$serializedColumn || !\is_array($value)) {
            return $value;
        }

        if (array_key_exists($serializedColumn, $value)) {
            return $value[$serializedColumn];
        }
        if (null !== $value) {
            throw new \UnexpectedValueException(
                "Unknown serialized format for entity #{$entity->getId()}"
            );
        }

        return $value;
    }
}
