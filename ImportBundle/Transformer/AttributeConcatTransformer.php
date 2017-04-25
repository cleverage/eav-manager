<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;


use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

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
        throw new MethodNotImplementedException(__METHOD__);
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
