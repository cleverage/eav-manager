<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;

use CleverAge\EAVManager\ImportBundle\Exception\TransformerNotImplementedException;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Transform a value array into a string, with a specific glue
 */
class AttributeConcatTransformer implements EAVValueTransformerInterface
{

    /** @var string */
    protected $glue = '';

    /**
     * @param string $glue
     */
    public function setGlue(string $glue)
    {
        $this->glue = $glue;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(FamilyInterface $family, AttributeInterface $attribute, $value, array $config = null)
    {
        throw new TransformerNotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform(
        FamilyInterface $family,
        AttributeInterface $attribute,
        $values,
        array $config = null
    ) {
        return implode($this->glue, $values);
    }

}
