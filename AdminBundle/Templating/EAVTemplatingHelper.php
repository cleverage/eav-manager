<?php

namespace CleverAge\EAVManager\AdminBundle\Templating;

use Sidus\AdminBundle\Admin\Action;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EAV extension to CDM admin templating helper
 */
class EAVTemplatingHelper
{
    /** @var TemplatingHelper */
    protected $baseTemplatingHelper;

    /**
     * @param TemplatingHelper $baseTemplatingHelper
     */
    public function __construct(TemplatingHelper $baseTemplatingHelper)
    {
        $this->baseTemplatingHelper = $baseTemplatingHelper;
    }

    /**
     * @param Action $action
     * @param array  $parameters
     *
     * @return Response
     */
    public function renderAction(Action $action, array $parameters = []): Response
    {
        return $this->baseTemplatingHelper->renderAction($action, $parameters);
    }

    /**
     * @param Action          $action
     * @param Request         $request
     * @param FamilyInterface $family
     * @param DataGrid        $dataGrid
     * @param array           $viewParameters
     *
     * @return Response
     */
    public function renderListAction(
        Action $action,
        Request $request,
        FamilyInterface $family,
        DataGrid $dataGrid,
        array $viewParameters = []
    ): Response {
        $viewParameters = array_merge(
            $this->getViewParameters($action, $request, $family),
            ['datagrid' => $dataGrid],
            $viewParameters
        );

        return $this->renderAction($action, $viewParameters);
    }

    /**
     * @param Action          $action
     * @param Request         $request
     * @param FamilyInterface $family
     * @param FormInterface   $form
     * @param null            $data
     * @param array           $viewParameters
     *
     * @return Response
     */
    public function renderFormAction(
        Action $action,
        Request $request,
        FamilyInterface $family,
        FormInterface $form,
        $data = null,
        array $viewParameters = []
    ): Response {
        $viewParameters = array_merge(
            $this->getViewParameters($action, $request, $family, $form, $data),
            $viewParameters
        );

        return $this->renderAction($action, $viewParameters);
    }

    /**
     * @param Action          $action
     * @param Request         $request
     * @param FamilyInterface $family
     * @param FormInterface   $form
     * @param mixed           $data
     * @param array           $listRouteParameters
     *
     * @return array
     */
    public function getViewParameters(
        Action $action,
        Request $request,
        FamilyInterface $family,
        FormInterface $form = null,
        $data = null,
        array $listRouteParameters = []
    ): array {
        $listRouteParameters['familyCode'] = $family->getCode();
        $parameters = $this->baseTemplatingHelper->getViewParameters(
            $action,
            $request,
            $form,
            $data,
            $listRouteParameters
        );
        $parameters['family'] = $family;

        return $parameters;
    }
}
