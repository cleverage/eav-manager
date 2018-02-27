<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
            throw $this->createNotFoundException("No actual media associated to image #{$data->getId()}");
        }

        return $this->get('liip_imagine.controller')->filterAction($request, $image->getPath(), $filter);
    }
}
