<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Action\EAV;

use CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\BaseBundle\Doctrine\RepositoryFinder;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('read', family)")
 */
class ReadSingletonAction implements ActionInjectableInterface
{
    /** @var ReadAction */
    protected $readAction;

    /** @var RepositoryFinder */
    protected $repositoryFinder;

    /** @var Action */
    protected $action;

    /**
     * @param ReadAction       $readAction
     * @param RepositoryFinder $repositoryFinder
     */
    public function __construct(
        ReadAction $readAction,
        RepositoryFinder $repositoryFinder
    ) {
        $this->readAction = $readAction;
        $this->repositoryFinder = $repositoryFinder;
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
        /** @var DataRepository $repository */
        $repository = $this->repositoryFinder->getRepository($family->getDataClass());
        $data = $repository->getInstance($family);

        return ($this->readAction)($request, $data, $family);
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
        $this->readAction->setAction($action);
    }
}
