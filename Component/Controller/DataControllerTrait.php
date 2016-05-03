<?php

namespace CleverAge\EAVManager\Component\Controller;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Exception;
use LogicException;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;

/**
 * @method Registry getDoctrine
 * @method User getUser
 * @method addFlash($key, $message)
 * @property ContainerInterface $container
 */
trait DataControllerTrait
{
    /** @var FamilyInterface */
    protected $family;

    /**
     * @param string $familyCode
     * @return FamilyInterface
     * @throws Exception
     */
    protected function getFamily($familyCode)
    {
        return $this->container->get('sidus_eav_model.family_configuration.handler')->getFamily($familyCode);
    }

    /**
     * @param                      $id
     * @param FamilyInterface|null $family
     * @return DataInterface
     * @throws Exception
     */
    protected function getData($id, FamilyInterface $family = null)
    {
        if ($id instanceof DataInterface) {
            $data = $id;
        } else {
            /** @var DataInterface $data */
            if ($family) {
                $data = $this->getDoctrine()->getRepository($family->getDataClass())->find($id);
            } else {
                $data = $this->container->get('sidus_eav_model.doctrine.repository.data')->find($id);
            }
            if (!$data) {
                throw new NotFoundHttpException("No data found with id : {$id}");
            }
        }
        if (!$family) {
            $family = $data->getFamily();
        }
        $this->initDataFamily($family, $data);

        return $data;
    }

    /**
     * @param FamilyInterface $family
     * @param DataInterface   $data
     * @return FamilyInterface
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    protected function initDataFamily(FamilyInterface $family, DataInterface $data)
    {
        if ($family->getCode() !== $data->getFamilyCode()) {
            throw new UnexpectedValueException("Data family '{$data->getFamilyCode()}'' not matching admin family {$family->getCode()}");
        }
        $this->family = $family;
    }
}
