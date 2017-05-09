<?php

namespace CleverAge\EAVManager\AssetBundle\Controller;

use CleverAge\EAVManager\AssetBundle\Entity\Image;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MediaController
 *
 * @package CleverAge\EAVManager\AssetBundle\Controller
 */
class MediaController extends Controller
{
    /**
     * @param Request       $request
     * @param DataInterface $data
     * @param string        $filter
     *
     * @return Response
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
