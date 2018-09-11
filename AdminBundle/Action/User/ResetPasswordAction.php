<?php

namespace CleverAge\EAVManager\AdminBundle\Action\User;

use CleverAge\EAVManager\AdminBundle\Form\FormHelper;
use CleverAge\EAVManager\AdminBundle\Templating\TemplatingHelper;
use CleverAge\EAVManager\UserBundle\Domain\Manager\UserManagerInterface;
use CleverAge\EAVManager\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Doctrine\DoctrineHelper;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class ResetPasswordAction implements ActionInjectableInterface
{
    /** @var UserManagerInterface */
    protected $userManager;

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
     * @param Request $request
     * @param User    $user
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function __invoke(Request $request, User $user)
    {
        $form = $this->formHelper->getEmptyForm($this->action, $request, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->requestNewPassword($user);
            $this->doctrineHelper->saveEntity($this->action, $user, $request->getSession());

            if ($request->isXmlHttpRequest()) {
                return $this->templatingHelper->renderAction(
                    $this->action,
                    array_merge(
                        $this->templatingHelper->getViewParameters($this->action, $request),
                        [
                            'dataId' => $user->getId(),
                            'success' => 1,
                        ]
                    )
                );
            }

            return $this->routingHelper->redirectToAction(
                $this->action->getAdmin()->getAction(
                    $this->action->getOption('redirect_action', 'list')
                )
            );
        }

        return $this->templatingHelper->renderFormAction(
            $this->action,
            $request,
            $form,
            $user,
            [
                'dataId' => $user->getId(),
            ]
        );
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }
}
