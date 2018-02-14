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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic admin controller for non-EAV data.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class GenericAdminController extends AbstractAdminController
{
    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $dataGrid = $this->getDataGrid();

        $this->bindDataGridRequest($dataGrid, $request);

        return $this->renderAction(
            array_merge(
                $this->getViewParameters($request),
                ['datagrid' => $dataGrid]
            )
        );
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response
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
     * @throws \Exception
     *
     * @return Response
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

            $parameters = $request->query->all();
            $parameters['success'] = 1;

            return $this->redirectToEntity($data, 'edit', $parameters);
        }

        return $this->renderAction($this->getViewParameters($request, $form, $data));
    }

    /**
     * @param Request $request
     * @param mixed   $data
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function cloneAction(Request $request, $data = null)
    {
        return $this->editAction($request, clone $data);
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response
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
}
