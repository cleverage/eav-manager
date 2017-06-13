<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Specific controller for create/edit inline operations (see _data admin)
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
        $parameters = array_merge($request->query->all(), [
            'familyCode' => $data->getFamilyCode(),
            'id' => $data->getId(),
        ]);

        return $this->redirectToAction('edit', $parameters);
    }
}
