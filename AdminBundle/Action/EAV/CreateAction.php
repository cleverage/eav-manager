<?php

namespace CleverAge\EAVManager\AdminBundle\Action\EAV;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('create', family)")
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
