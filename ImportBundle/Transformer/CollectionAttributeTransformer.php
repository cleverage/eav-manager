<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Simple transformer for collection attributes.
 *
 * @deprecated we may use the new exploder transformer
 */
class CollectionAttributeTransformer implements EAVValueTransformerInterface
{
    /**
     * @param FamilyInterface    $family
     * @param AttributeInterface $attribute
     * @param mixed              $value
     * @param array              $config
     *
     * @throws \UnexpectedValueException
     *
     * @return mixed
     */
    public function transform(FamilyInterface $family, AttributeInterface $attribute, $value, array $config = null)
    {
        $this->checkConfig($attribute, $config);
        if (null === $value) { // Skip case where value is empty
            return null;
        }
        if (!is_array($value)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new \UnexpectedValueException("Expecting an array, given '{$type}'");
        }

        return implode($config['splitCharacter'], $value); // Will crash if value is not an array of scalar
    }

    /**
     * @param FamilyInterface    $family
     * @param AttributeInterface $attribute
     * @param mixed              $value
     * @param array              $config
     *
     * @throws \UnexpectedValueException
     *
     * @return mixed
     */
    public function reverseTransform(
        FamilyInterface $family,
        AttributeInterface $attribute,
        $value,
        array $config = null
    ) {
        $this->checkConfig($attribute, $config);
        if (null === $value || '' === $value) { // Skip case where value is empty
            return [];
        }

        return explode($config['splitCharacter'], $value);
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $config
     *
     * @throws \UnexpectedValueException
     */
    protected function checkConfig(AttributeInterface $attribute, array $config = null)
    {
        if (!isset($config['splitCharacter'])) {
            $m = "Collection attribute '{$attribute->getCode()}' expects a splitCharacter option in import mapping";
            throw new \UnexpectedValueException($m);
        }
    }
}
