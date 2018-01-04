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

use Sidus\EAVModelBundle\Entity\DataInterface;
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
}
