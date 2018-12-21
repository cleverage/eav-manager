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

use CleverAge\EAVManager\AdminBundle\Templating\EAVTemplatingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use CleverAge\EAVManager\AdminBundle\DataGrid\EAVDataGridHelper;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Security("is_granted('list', family)")
 */
class ListAction implements ActionInjectableInterface
{
    /** @var EAVDataGridHelper */
    protected $dataGridHelper;

    /** @var EAVTemplatingHelper */
    protected $templatingHelper;

    /** @var RouterInterface */
    protected $router;

    /** @var Action */
    protected $action;

    /**
     * @param EAVDataGridHelper   $dataGridHelper
     * @param EAVTemplatingHelper $templatingHelper
     * @param RouterInterface     $router
     */
    public function __construct(
        EAVDataGridHelper $dataGridHelper,
        EAVTemplatingHelper $templatingHelper,
        RouterInterface $router
    ) {
        $this->dataGridHelper = $dataGridHelper;
        $this->templatingHelper = $templatingHelper;
        $this->router = $router;
    }

    /**
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function __invoke(Request $request, FamilyInterface $family)
    {
        $this->router->getContext()->setParameter('familyCode', $family->getCode());
        $dataGrid = $this->dataGridHelper->bindDataGridRequest($this->action, $request, $family);

        return $this->templatingHelper->renderListAction($this->action, $request, $family, $dataGrid);
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }
}
