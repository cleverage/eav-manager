<?php

namespace CleverAge\EAVManager\AdminBundle\Action\EAV;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("(is_granted('create', family) and is_granted('read', data))")
 */
class CloneAction implements ActionInjectableInterface
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
     * @param DataInterface   $data
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function __invoke(Request $request, DataInterface $data, FamilyInterface $family = null): Response
    {
        $this->editAction->setAction($this->action);

        return ($this->editAction)($request, clone $data, $family);
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }
}
