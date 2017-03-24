<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\Component\Controller\BaseControllerTrait;
use Elastica\Query;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Controller\AbstractAdminController as BaseAdminController;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\EAVModelBundle\Translator\TranslatableTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Sidus\AdminBundle\Routing\AdminRouter;
use Symfony\Component\Form\Form;

/**
 * {@inheritdoc}
 */
abstract class AbstractAdminController extends BaseAdminController
{
    use BaseControllerTrait;
    use TranslatableTrait;

    /** @var string */
    protected $defaultTarget = '#tg_center';

    /**
     * @param ContainerInterface|null $container
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->translator = $container->get('translator'); // Specifically inject translator for tryTranslate method
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getTarget(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->defaultTarget;
        }

        return $request->get('target', $this->defaultTarget);
    }

    /**
     * @param Request $request
     * @param Form    $form
     * @param mixed   $data
     *
     * @return array
     * @throws \Exception
     */
    protected function getViewParameters(Request $request, Form $form = null, $data = null)
    {
        $parameters = [
            'isAjax' => $request->isXmlHttpRequest(),
            'target' => $request->get('target'),
            'success' => $request->get('success'),
            'isModal' => $request->isXmlHttpRequest() && $request->get('modal'),
            'listPath' => $this->getAdminListPath($data),
            'admin' => $this->admin,
        ];

        if ($form) {
            $parameters['form'] = $form->createView();
        }
        if ($data) {
            $parameters['data'] = $data;
        }

        return $parameters;
    }

    /**
     * @param mixed $data
     * @param array $parameters
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getAdminListPath($data = null, array $parameters = [])
    {
        if (!$this->admin->hasAction('list')) {
            return null;
        }

        /** @var AdminRouter $adminRouter */
        $adminRouter = $this->get('sidus_admin.routing.admin_router');

        return $adminRouter->generateAdminPath($this->admin, 'list', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function bindDataGridRequest(DataGrid $dataGrid, Request $request, array $formOptions = [])
    {
        $formOptions = array_merge(
            [
                'attr' => [
                    'data-target-element' => $this->getTarget($request),
                ],
            ],
            $formOptions
        );

        parent::bindDataGridRequest($dataGrid, $request, $formOptions);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormOptions(Request $request, $dataId, Action $action = null)
    {
        if (!$action) {
            $action = $this->admin->getCurrentAction();
        }

        $formOptions = parent::getDefaultFormOptions($request, $dataId, $action);

        if ($request->isXmlHttpRequest()) { // Target should not be used when not calling through Ajax
            $formOptions['attr']['data-target-element'] = $this->getTarget($request);
        }
        $formOptions['label'] = $this->tryTranslate(
            [
                "admin.{$this->admin->getCode()}.{$action->getCode()}.title",
                "admin.action.{$action->getCode()}.title",
            ],
            [],
            ucfirst($action->getCode())
        );

        return $formOptions;
    }
}
