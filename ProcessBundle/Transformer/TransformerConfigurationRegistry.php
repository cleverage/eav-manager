<?php

namespace CleverAge\EAVManager\ProcessBundle\Transformer;

/**
 * @TODO describe class usage
 */
class TransformerConfigurationRegistry
{
    /** @var array TransformerConfiguration[] */
    protected $configs = [];

    /**
     * @param TransformerConfiguration $configuration
     */
    public function addTransformerConfiguration(TransformerConfiguration $configuration)
    {
        $this->configs[$configuration->getCode()] = $configuration;
    }

    /**
     * @param string $code
     *
     * @return TransformerConfiguration
     */
    public function getTransformerConfiguration(string $code): TransformerConfiguration
    {
        return $this->configs[$code];
    }
}
