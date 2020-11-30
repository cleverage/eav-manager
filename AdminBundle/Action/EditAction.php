<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Action;

use CleverAge\EAVManager\AdminBundle\Templating\TemplatingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Action\RedirectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Doctrine\DoctrineHelper;
use CleverAge\EAVManager\AdminBundle\Form\FormHelper;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('edit', data)")
 */
class EditAction implements ActionInjectableInterface, RedirectableInterface
{
    /** @var FormHelper */
    protected $formHelper;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RoutingHelper */
    protected $routingHelper;

    /** @var TemplatingHelper */
    protected $templatingHelper;

    /** @var Action */
    protected $action;

    /** @var Action */
    protected $redirectAction;

    /**
     * @param FormHelper       $formHelper
     * @param DoctrineHelper   $doctrineHelper
     * @param RoutingHelper    $routingHelper
     * @param TemplatingHelper $templatingHelper
     */
    public function __construct(
        FormHelper $formHelper,
        DoctrineHelper $doctrineHelper,
        RoutingHelper $routingHelper,
        TemplatingHelper $templatingHelper
    ) {
        $this->formHelper = $formHelper;
        $this->doctrineHelper = $doctrineHelper;
        $this->routingHelper = $routingHelper;
        $this->templatingHelper = $templatingHelper;
    }

    /**
     * @ParamConverter(name="data", converter="sidus_admin.entity")
     *
     * @param Request $request
     * @param mixed   $data
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function __invoke(Request $request, $data): Response
    {
        $form = $this->formHelper->getForm($this->action, $request, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrineHelper->saveEntity($this->action, $data, $request->getSession());

            $parameters = $request->query->all();
            $parameters['success'] = 1;

            return $this->routingHelper->redirectToEntity($this->redirectAction, $data, $parameters);
        }

        return $this->templatingHelper->renderFormAction($this->action, $request, $form, $data);
    }

    /**
     * @param Action $action
     */
    public function setRedirectAction(Action $action): void
    {
        $this->redirectAction = $action;
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
        $this->redirectAction = $action;
    }
}
