<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Transforms data for the EAV model
 */
interface EAVDataTransformerInterface
{
    /**
     * @param FamilyInterface $family
     * @param DataInterface   $data
     * @param array           $config
     *
     * @return mixed
     */
    public function transform(FamilyInterface $family, DataInterface $data, array $config = null);

    /**
     * @param FamilyInterface $family
     * @param array           $data
     * @param array           $config
     *
     * @return mixed
     */
    public function reverseTransform(FamilyInterface $family, $data, array $config = null);
}
