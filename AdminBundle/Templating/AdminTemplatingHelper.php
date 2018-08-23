<?php

namespace CleverAge\EAVManager\AdminBundle\Templating;

use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Templating\TemplatingHelper as BaseAdminTemplatingHelper;
use Sidus\DataGridBundle\Model\DataGrid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clever Data Manager extension to base admin templating helper
 */
class AdminTemplatingHelper
{
    /** @var BaseAdminTemplatingHelper */
    protected $baseTemplatingHelper;

    /**
     * @param BaseAdminTemplatingHelper $baseTemplatingHelper
     */
    public function __construct(BaseAdminTemplatingHelper $baseTemplatingHelper)
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
     * @param Action   $action
     * @param Request  $request
     * @param DataGrid $dataGrid
     * @param array    $viewParameters
     *
     * @return Response
     */
    public function renderListAction(
        Action $action,
        Request $request,
        DataGrid $dataGrid,
        array $viewParameters = []
    ): Response {
        $viewParameters = array_merge(
            $this->getViewParameters($action, $request),
            ['datagrid' => $dataGrid],
            $viewParameters
        );

        return $this->renderAction($action, $viewParameters);
    }

    /**
     * @param Action        $action
     * @param Request       $request
     * @param FormInterface $form
     * @param null          $data
     * @param array         $viewParameters
     *
     * @return Response
     */
    public function renderFormAction(
        Action $action,
        Request $request,
        FormInterface $form,
        $data = null,
        array $viewParameters = []
    ): Response {
        $viewParameters = array_merge($this->getViewParameters($action, $request, $form, $data), $viewParameters);

        return $this->renderAction($action, $viewParameters);
    }

    /**
     * @param Action        $action
     * @param Request       $request
     * @param FormInterface $form
     * @param mixed         $data
     * @param array         $listRouteParameters
     *
     * @return array
     */
    public function getViewParameters(
        Action $action,
        Request $request,
        FormInterface $form = null,
        $data = null,
        array $listRouteParameters = []
    ): array {
        $baseParameters = $this->baseTemplatingHelper->getViewParameters($action, $form, $data, $listRouteParameters);
        $parameters = [
            'isAjax' => $request->isXmlHttpRequest(),
            'target' => $request->get('target'),
            'success' => $request->get('success'),
            'isModal' => $request->isXmlHttpRequest() && $request->get('modal'),
        ];

        return array_merge($baseParameters, $parameters);
    }
}
