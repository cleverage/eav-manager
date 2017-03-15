<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic admin controller for non-EAV data
 */
class GenericAdminController extends AbstractAdminController
{
    /**
     * @param Request $request
     *
     * @return array
     * @throws \Exception
     */
    public function listAction(Request $request)
    {
        $dataGrid = $this->getDataGrid();

        $this->bindDataGridRequest($dataGrid, $request);

        return $this->renderAction(
            [
                'datagrid' => $dataGrid,
                'isAjax' => $request->isXmlHttpRequest(),
                'target' => $this->getTarget($request),
                'admin' => $this->admin,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $class = $this->admin->getEntity();
        $data = new $class();

        return $this->editAction($request, $data);
    }

    /**
     * @param Request $request
     * @param mixed   $data
     *
     * @return array|RedirectResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $data = null)
    {
        if (null === $data) {
            $data = $this->getDataFromRequest($request);
        }
        $form = $this->getForm($request, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
     * @param Request $request
     *
     * @return array|RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(Request $request)
    {
        $data = $this->getDataFromRequest($request);
        $builder = $this->createFormBuilder(null, $this->getDefaultFormOptions($request, $data->getId()));
        $form = $builder->getForm();
        $dataId = $data->getId();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->deleteEntity($data);
            if ($request->isXmlHttpRequest()) {
                return $this->renderAction(
                    [
                        'dataGridCode' => $this->getDataGridConfigCode(),
                        'dataId' => $dataId,
                        'isAjax' => 1,
                        'target' => $request->get('target'),
                        'success' => 1,
                    ]
                );
            }

            return $this->redirectToAdmin($this->admin, 'list');
        }

        return $this->renderAction(
            $this->getViewParameters($request, $form, $data) + [
                'dataId' => $dataId,
            ]
        );
    }
}
