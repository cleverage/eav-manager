<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\Component\Controller;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Persistence\ManagerRegistry;
use CleverAge\EAVManager\UserBundle\Entity\User;

/**
 * @method ManagerRegistry getDoctrine
 * @method User getUser
 * @method addFlash($key, $message)
 *
 * @property \Symfony\Component\DependencyInjection\ContainerInterface $container
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
trait EAVDataControllerTrait
{
    /** @var FamilyInterface */
    protected $family;

    /**
     * @param string $familyCode
     *
     * @return FamilyInterface
     *
     * @throws \Exception
     */
    protected function getFamily($familyCode): FamilyInterface
    {
        return $this->container->get(FamilyRegistry::class)->getFamily($familyCode);
    }

    /**
     * @param FamilyInterface $family
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    protected function setFamily(FamilyInterface $family)
    {
        $this->family = $family;
        $this->container->get('router')->getContext()->setParameter('familyCode', $family->getCode());
    }

    /**
     * @param string|int           $id
     * @param FamilyInterface|null $family
     *
     * @throws \Exception
     *
     * @return DataInterface
     */
    protected function getData($id, FamilyInterface $family = null): DataInterface
    {
        if ($id instanceof DataInterface) {
            $data = $id;
        } else {
            $dataClass = $this->container->getParameter('sidus_eav_model.entity.data.class');
            if ($family) {
                $dataClass = $family->getDataClass();
            }
            /** @var DataInterface $data */
            $data = $this->getDoctrine()->getRepository($dataClass)->find($id);

            if (!$data) {
                throw new NotFoundHttpException("No data found with id : {$id}");
            }
        }
        if (!$family) {
            $family = $data->getFamily();
        }
        $this->initDataFamily($data, $family);

        return $data;
    }

    /**
     * @param DataInterface   $data
     * @param FamilyInterface $family
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    protected function initDataFamily(DataInterface $data, FamilyInterface $family = null)
    {
        if (!$family) {
            $family = $data->getFamily();
        } elseif ($family->getCode() !== $data->getFamilyCode()) {
            throw new \UnexpectedValueException(
                "Data family '{$data->getFamilyCode()}'' not matching admin family {$family->getCode()}"
            );
        }
        $this->setFamily($family);
    }
}
