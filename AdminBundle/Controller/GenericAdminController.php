<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
