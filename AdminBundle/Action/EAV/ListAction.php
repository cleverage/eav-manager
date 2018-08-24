<?php

namespace CleverAge\EAVManager\AdminBundle\Action\EAV;

use CleverAge\EAVManager\AdminBundle\Templating\EAVAdminTemplatingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use CleverAge\EAVManager\AdminBundle\DataGrid\DataGridHelper;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Security("is_granted('ROLE_DATA_MANAGER')")
 */
class ListAction implements ActionInjectableInterface
{
    /** @var DataGridHelper */
    protected $dataGridHelper;

    /** @var EAVAdminTemplatingHelper */
    protected $templatingHelper;

    /** @var RouterInterface */
    protected $router;

    /** @var Action */
    protected $action;

    /**
     * @param DataGridHelper           $dataGridHelper
     * @param EAVAdminTemplatingHelper $templatingHelper
     * @param RouterInterface          $router
     */
    public function __construct(
        DataGridHelper $dataGridHelper,
        EAVAdminTemplatingHelper $templatingHelper,
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
        $dataGrid = $this->dataGridHelper->bindDataGridRequest($this->action, $request);

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
