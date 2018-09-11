<?php

namespace CleverAge\EAVManager\AdminBundle\Action;

use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo is_granted('create', entityClass)
 */
class CreateAction implements ActionInjectableInterface
{
    /** @var EditAction */
    protected $editAction;

    /** @var Action */
    protected $action;

    /**
     * @param EditAction $editAction
     */
    public function __construct(EditAction $editAction)
    {
        $this->editAction = $editAction;
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $this->editAction->setAction($this->action);
        $class = $this->action->getAdmin()->getEntity();

        return ($this->editAction)($request, new $class());
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }
}
