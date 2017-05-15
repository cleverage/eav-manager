<?php

namespace CleverAge\EAVManager\ProcessBundle\Transformer;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;

/**
 * @TODO describe class usage
 */
class TransformerProcess implements ProcessInterface
{

    /** @var TransformerConfiguration */
    protected $transformerConfig;

    /** @var array */
    protected $originalData;

    /** @var array */
    protected $transformedData;

    /**
     * TransformerProcess constructor.
     *
     * @param TransformerConfigurationRegistry $transformerConfigRegistry
     * @param string                           $transformerConfigName
     */
    public function __construct(
        TransformerConfigurationRegistry $transformerConfigRegistry,
        string $transformerConfigName
    ) {
        $this->transformerConfig = $transformerConfigRegistry->getTransformerConfiguration($transformerConfigName);
    }

    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        $this->originalData = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var TransformerManager $manager */
        $manager = $this->transformerConfig->getService();
        $this->transformedData = $manager->transform($this->transformerConfig->getMapping(), $this->originalData);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->transformedData;
    }

}
