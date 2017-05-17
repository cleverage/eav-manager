<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Allow to chain a few Value transformers.
 */
class ChainedValueTransformer implements EAVValueTransformerInterface
{
    /** @var EAVValueTransformerInterface[] */
    protected $transformers;

    /**
     * @param EAVValueTransformerInterface[] ...$transformers
     */
    public function __construct(EAVValueTransformerInterface ...$transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(FamilyInterface $family, AttributeInterface $attribute, $value, array $config = null)
    {
        /** @var EAVValueTransformerInterface[] $reversedTransformers */
        $reversedTransformers = array_reverse($this->transformers);
        foreach ($reversedTransformers as $transformer) {
            $value = $transformer->transform($family, $attribute, $value, $config);
        }

        return $value;
    }

    /**
     * Keep the same order than defined in config (because it's the main used)
     * {@inheritdoc}
     */
    public function reverseTransform(
        FamilyInterface $family,
        AttributeInterface $attribute,
        $value,
        array $config = null
    ) {
        foreach ($this->transformers as $transformer) {
            $value = $transformer->reverseTransform($family, $attribute, $value, $config);
        }

        return $value;
    }
}
