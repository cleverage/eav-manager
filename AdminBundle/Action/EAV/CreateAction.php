<?php

namespace CleverAge\EAVManager\AdminBundle\Action\EAV;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Security("is_granted('create', family)")
 */
class CreateAction implements ActionInjectableInterface
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
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function __invoke(Request $request, FamilyInterface $family): Response
    {
        $this->editAction->setAction($this->action);
        $admin = $this->action->getAdmin();

        foreach (['edit', 'read'] as $actionCode) {
            if ($admin->hasAction($actionCode) && $this->authorizationChecker->isGranted($actionCode, $family)) {
                $this->editAction->setRedirectAction($admin->getAction($actionCode));
                break;
            }
        }

        return ($this->editAction)($request, $family->createData(), $family);
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }
}
