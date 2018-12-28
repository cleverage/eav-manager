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

use CleverAge\EAVManager\AdminBundle\DataGrid\AttributeDataGridHelper;
use CleverAge\EAVManager\AdminBundle\Templating\TemplatingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * List entities pointed by an attribute relationship in a given EAV data
 *
 * @Security("is_granted('read', data)")
 */
class AttributeDataGridAction implements ActionInjectableInterface
{
    /** @var AttributeDataGridHelper */
    protected $attributeDataGridHelper;

    /** @var TemplatingHelper */
    protected $templatingHelper;

    /** @var RouterInterface */
    protected $router;

    /** @var Action */
    protected $action;

    /**
     * @param AttributeDataGridHelper $attributeDataGridHelper
     * @param TemplatingHelper        $templatingHelper
     * @param RouterInterface         $router
     */
    public function __construct(
        AttributeDataGridHelper $attributeDataGridHelper,
        TemplatingHelper $templatingHelper,
        RouterInterface $router
    ) {
        $this->attributeDataGridHelper = $attributeDataGridHelper;
        $this->templatingHelper = $templatingHelper;
        $this->router = $router;
    }

    /**
     * @param Request       $request
     * @param DataInterface $data
     * @param DataGrid      $dataGrid
     * @param string        $attributeCode
     *
     * @return Response
     */
    public function __invoke(
        Request $request,
        DataInterface $data,
        DataGrid $dataGrid,
        string $attributeCode
    ) {
        $attribute = $data->getFamily()->getAttribute($attributeCode);
        $this->attributeDataGridHelper->buildAttributeDataGrid($dataGrid, $data, $attribute);

        return $this->templatingHelper->renderListAction(
            $this->action,
            $request,
            $dataGrid,
            [
                'parent_data' => $data,
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
