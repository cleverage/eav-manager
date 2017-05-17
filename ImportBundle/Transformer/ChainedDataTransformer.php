<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Allow to chain a few Data transformers.
 */
class ChainedDataTransformer implements EAVDataTransformerInterface
{
    /** @var EAVDataTransformerInterface[] */
    protected $transformers;

    /**
     * @param EAVDataTransformerInterface[] ...$transformers
     */
    public function __construct(EAVDataTransformerInterface ...$transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(FamilyInterface $family, DataInterface $data, array $config = null)
    {
        /** @var EAVDataTransformerInterface[] $reversedTransformers */
        $reversedTransformers = array_reverse($this->transformers);
        foreach ($reversedTransformers as $transformer) {
            $data = $transformer->transform($family, $data, $config);
        }

        return $data;
    }

    /**
     * Keep the same order than defined in config (because it's the main used)
     * {@inheritdoc}
     */
    public function reverseTransform(FamilyInterface $family, $data, array $config = null)
    {
        foreach ($this->transformers as $transformer) {
            $data = $transformer->reverseTransform($family, $data, $config);
        }

        return $data;
    }
}
