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

namespace CleverAge\EAVManager\AssetBundle\Controller;

use CleverAge\EAVManager\AssetBundle\Entity\Image;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MediaController.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class MediaController extends Controller
{
    /**
     * @param Request       $request
     * @param DataInterface $data
     * @param string        $filter
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function mediaUrlAction(Request $request, DataInterface $data, $filter)
    {
        if ($data->getFamilyCode() !== 'Image') {
            throw new \UnexpectedValueException("Data should be of family 'Image', '{$data->getFamilyCode()}' given");
        }
        /** @var \Sidus\EAV\Image $data */
        $image = $data->getImageFile();
        if (!$image instanceof Image) {
            return $this->createNotFoundException("No actual media associated to image #{$data->getId()}");
        }

        return $this->get('liip_imagine.controller')->filterAction($request, $image->getPath(), $filter);
    }
}
