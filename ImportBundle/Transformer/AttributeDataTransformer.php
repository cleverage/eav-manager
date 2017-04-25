<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Allow to use a Value Transformer on place of a Data transformer (during global transformation)
 */
class AttributeDataTransformer implements EAVDataTransformerInterface
{

    /** @var EAVValueTransformerInterface */
    protected $eavValueTransformer;

    /** @var string */
    protected $attributeCode;

    /**
     * AttributeDataTransformer constructor.
     * @param EAVValueTransformerInterface $eavValueTransformer
     * @param string                       $attributeCode
     */
    public function __construct(EAVValueTransformerInterface $eavValueTransformer, $attributeCode)
    {
        $this->eavValueTransformer = $eavValueTransformer;
        $this->attributeCode = $attributeCode;
    }


    /**
     * {@inheritdoc}
     */
    public function transform(FamilyInterface $family, DataInterface $data, array $config = null)
    {
        $data->set(
            $this->attributeCode,
            $this->eavValueTransformer->reverseTransform(
                $family,
                $family->getAttribute($this->attributeCode),
                $data->get($this->attributeCode),
                $config
            )
        );

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform(FamilyInterface $family, $data, array $config = null)
    {
        $data[$this->attributeCode] = $this->eavValueTransformer->reverseTransform(
            $family,
            $family->getAttribute($this->attributeCode),
            $data[$this->attributeCode],
            $config
        );

        return $data;
    }


}
