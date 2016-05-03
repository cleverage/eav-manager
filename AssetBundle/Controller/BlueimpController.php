<?php

namespace CleverAge\EAVManager\AssetBundle\Controller;

use Oneup\UploaderBundle\Controller\BlueimpController as BaseBlueimpController;
use Oneup\UploaderBundle\Uploader\Response\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

class BlueimpController extends BaseBlueimpController
{
    /** @var Request */
    protected $request;

    /**
     * @param                   $file
     * @param ResponseInterface $response
     * @param Request|null      $request
     * @return Resource
     * @throws \UnexpectedValueException
     */
    public function handleManualUpload($file, ResponseInterface $response, Request $request = null)
    {
        if (!$request) {
            $request = new Request();
        }
        $this->setRequest($request);
        $this->handleUpload($file, $response, $request);
        $files = $response->assemble();
        if (0 === count($files)) {
            throw new \UnexpectedValueException('File upload returned empty response');
        }

        return array_pop($files);
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    protected function getRequest()
    {
        if ($this->request) {
            return $this->request;
        }

        return parent::getRequest();
    }
}
