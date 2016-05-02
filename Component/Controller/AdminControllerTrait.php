<?php

namespace CleverAge\EAVManager\Component\Controller;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Admin\Admin;
use Symfony\Component\Form\Form;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method addFlash($key, $message)
 * @method Form createForm($type, $data = null, array $options = [])
 * @method string getCurrentUri(Request $request, array $parameters = [])
 * @method EntityManager getManager($persistentManagerName = null)
 * @property ContainerInterface $container
 */
trait AdminControllerTrait
{
    /** @var Admin */
    protected $admin;

    /**
     * @param Admin $admin
     */
    public function setAdmin(Admin $admin)
    {
        $this->admin = $admin;
    }

    /**
     * @param Request $request
     * @param mixed $data
     * @param array $options
     * @return Form
     * @throws \InvalidArgumentException
     */
    protected function getForm(Request $request, $data, array $options = [])
    {
        $action = $this->admin->getCurrentAction();
        $defaultOptions = $this->getDefaultFormOptions($request, $data ? $data->getId() : 'new');
        return $this->createForm($action->getFormType(), $data, array_merge($defaultOptions, $options));
    }

    /**
     * @param mixed $data
     * @throws \Exception
     */
    protected function saveEntity($data)
    {
        $em = $this->getManager();
        $em->persist($data);
        $em->flush();

        $action = $this->admin->getCurrentAction();
        $this->addFlash('success', "admin.flash.{$action->getCode()}.success");
    }

    /**
     * @param mixed $data
     * @throws \Exception
     */
    protected function deleteEntity($data)
    {
        $em = $this->getManager();
        $em->remove($data);
        $em->flush();

        $action = $this->admin->getCurrentAction();
        $this->addFlash('success', "admin.flash.{$action->getCode()}.success");
    }

    /**
     * @param Request $request
     * @param Form $form
     * @param $data
     * @return array
     */
    protected function getViewParameters(Request $request, Form $form, $data)
    {
        return [
            'isAjax' => $request->isXmlHttpRequest(),
            'target' => $request->get('target'),
            'success' => $request->get('success'),
            'form' => $form->createView(),
            'data' => $data,
        ];
    }

    /**
     * @param Request $request
     * @param string $dataId
     * @param Action|null $action
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getDefaultFormOptions(Request $request, $dataId, Action $action = null)
    {
        if (!$action) {
            $action = $this->admin->getCurrentAction();
        }
        return [
            'label' => "admin.{$this->admin->getCode()}.{$action->getCode()}.title",
            'action' => $this->getCurrentUri($request),
            'attr' => [
                'novalidate' => 'novalidate',
                'data-target' => $request->get('target'),
                'id' => "form_{$this->admin->getCode()}_{$action->getCode()}_{$dataId}",
            ],
            'method' => 'post',
        ];
    }

    /**
     * @param Admin $admin
     * @param Action $action
     * @return \Twig_Template
     * @throws \Exception
     */
    protected function getTemplate(Admin $admin = null, Action $action = null)
    {
        return $this->container->get('sidus_admin.templating.template_resolver')->getTemplate($admin, $action);
    }

    /**
     * @param array $parameters
     * @param Admin|null $admin
     * @param Action|null $action
     * @return Response
     * @throws \Exception
     */
    protected function renderAction(array $parameters = [], Admin $admin = null, Action $action = null)
    {
        $response = new Response();
        $response->setContent($this->getTemplate($admin, $action)->render($parameters));
        return $response;
    }

    /**
     * @param Request $request
     * @param array|ParamConverterInterface $configuration
     * @return mixed
     * @throws \Exception
     */
    protected function getDataFromRequest(Request $request, $configuration = null)
    {
        if (null === $configuration) {
            $configuration = [
                new ParamConverter([
                    'name' => 'data',
                    'class' => $this->admin->getEntity(),
                ]),
            ];
        }
        $this->container->get('sensio_framework_extra.converter.manager')->apply($request, $configuration);
        return $request->attributes->get('data');
    }
}
