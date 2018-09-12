<?php

namespace CleverAge\EAVManager\AdminBundle\Action;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Security("(is_granted('read', data) and is_granted('create', _admin.getEntity()))")
 */
class CloneAction implements ActionInjectableInterface
{
    /** @var EditAction */
    protected $editAction;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var Action */
    protected $action;

    /**
     * @param EditAction                    $editAction
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(EditAction $editAction, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->editAction = $editAction;
        $this->authorizationChecker = $authorizationChecker;
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
        $this->editAction->setAction($this->action);
        $admin = $this->action->getAdmin();
        $class = $admin->getEntity();

        foreach (['edit', 'read'] as $actionCode) {
            if ($admin->hasAction($actionCode) && $this->authorizationChecker->isGranted($actionCode, $class)) {
                $this->editAction->setRedirectAction($admin->getAction($actionCode));
                break;
            }
        }

        return ($this->editAction)($request, clone $data);
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }
}
