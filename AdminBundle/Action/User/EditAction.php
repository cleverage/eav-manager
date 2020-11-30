<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Action\User;

use CleverAge\EAVManager\AdminBundle\Form\FormHelper;
use CleverAge\EAVManager\AdminBundle\Templating\TemplatingHelper;
use CleverAge\EAVManager\UserBundle\Domain\Manager\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Action\RedirectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Sidus\BaseBundle\Translator\TranslatableTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Security("is_granted('edit', data)")
 */
class EditAction implements ActionInjectableInterface, RedirectableInterface
{
    use TranslatableTrait;

    /** @var FormHelper */
    protected $formHelper;

    /** @var UserManagerInterface */
    protected $userManager;

    /** @var RoutingHelper */
    protected $routingHelper;

    /** @var TemplatingHelper */
    protected $templatingHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var Action */
    protected $action;

    /** @var Action */
    protected $redirectAction;


    /**
     * @param FormHelper           $formHelper
     * @param UserManagerInterface $userManager
     * @param RoutingHelper        $routingHelper
     * @param TemplatingHelper     $templatingHelper
     */
    public function __construct(
        FormHelper $formHelper,
        UserManagerInterface $userManager,
        RoutingHelper $routingHelper,
        TemplatingHelper $templatingHelper,
        TranslatorInterface $translator
    ) {
        $this->formHelper = $formHelper;
        $this->userManager = $userManager;
        $this->routingHelper = $routingHelper;
        $this->templatingHelper = $templatingHelper;
        $this->translator = $translator;
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
            $this->userManager->save($data);
            $this->addFlash($request->getSession());

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

    /**
     * @param SessionInterface|null $session
     */
    protected function addFlash(SessionInterface $session = null): void
    {
        if ($session instanceof Session) {
            $session->getFlashBag()->add(
                'success',
                $this->tryTranslate(
                    [
                        "admin.{$this->action->getAdmin()->getCode()}.{$this->action->getCode()}.success",
                        "admin.flash.{$this->action->getCode()}.success",
                    ],
                    [],
                    ucfirst($this->action->getCode()).' success'
                )
            );
        }
    }
}
