<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\DataGrid;

use Doctrine\ORM\Query\Expr\Join;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\DataGridBundle\Registry\DataGridRegistry;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\FilterBundle\Query\Handler\Doctrine\DoctrineQueryHandlerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Helper for attribute's datagrid
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class AttributeDataGridHelper
{
    /** @var DataGridRegistry */
    protected $dataGridRegistry;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /**
     * @param DataGridRegistry     $dataGridRegistry
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(DataGridRegistry $dataGridRegistry, FormFactoryInterface $formFactory)
    {
        $this->dataGridRegistry = $dataGridRegistry;
        $this->formFactory = $formFactory;
    }

    /**
     * @param DataGrid           $dataGrid
     * @param DataInterface      $parentData
     * @param AttributeInterface $attribute
     */
    public function buildAttributeDataGrid(
        DataGrid $dataGrid,
        DataInterface $parentData,
        AttributeInterface $attribute
    ): void {
        $attributeType = $attribute->getType();
        if (!$attributeType->isRelation() && !$attributeType->isEmbedded()) {
            throw new \UnexpectedValueException(
                "Attribute {$parentData->getFamilyCode()}.{$attribute->getCode()} is not a relation neither an embed attribute"
            );
        }

        $dataGrid->buildForm($this->formFactory->createBuilder());
        $queryHandler = $dataGrid->getQueryHandler();
        if (!$queryHandler instanceof DoctrineQueryHandlerInterface) {
            throw new \UnexpectedValueException('Wrong query handler type');
        }
        $qb = $queryHandler->getQueryBuilder();
        $qb
            ->join(
                $queryHandler->getAlias().'.refererValues',
                '_ref',
                Join::WITH,
                '(_ref.attributeCode = :attributeCode AND _ref.data = :parentData)'
            )
            ->setParameter('attributeCode', $attribute->getCode())
            ->setParameter('parentData', $parentData);
    }
}
