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

use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Specific controller for create/edit inline operations (see _data admin)
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class InlineEAVDataController extends EAVDataController
{
    /**
     * @param Request       $request
     * @param DataInterface $data
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function editInlineAction(Request $request, DataInterface $data)
    {
        $parameters = array_merge(
            $request->query->all(),
            [
                'familyCode' => $data->getFamilyCode(),
                'id' => $data->getId(),
            ]
        );

        return $this->redirectToAction('edit', $parameters);
    }

    /**
     * Alias for edit action but with custom form options
     *
     * @param Request       $request
     * @param DataInterface $data
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function previewAction(Request $request, DataInterface $data)
    {
        return $this->editAction($request, $data, $data->getFamily());
    }

    /**
     * @param Request $request
     * @param Form    $form
     * @param mixed   $data
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function getViewParameters(Request $request, Form $form = null, $data = null): array
    {
        $parameters = parent::getViewParameters($request, $form, $data);
        if ('preview' === $this->admin->getCurrentAction()->getCode()) {
            $parameters['disabled'] = $request->query->getBoolean('disabled');
        }

        return $parameters;
    }
}
