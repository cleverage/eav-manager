<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Transforms data for the EAV model
 */
interface EAVValueTransformerInterface
{
    /**
     * @param FamilyInterface    $family
     * @param AttributeInterface $attribute
     * @param mixed              $value
     * @param array              $config
     *
     * @return mixed
     */
    public function transform(FamilyInterface $family, AttributeInterface $attribute, $value, array $config = null);

    /**
     * @param FamilyInterface    $family
     * @param AttributeInterface $attribute
     * @param mixed              $value
     * @param array              $config
     *
     * @return mixed
     */
    public function reverseTransform(FamilyInterface $family, AttributeInterface $attribute, $value, array $config = null);
}
