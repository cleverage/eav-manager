<?php

namespace CleverAge\EAVManager\ProcessBundle\Transformer;

/**
 * @TODO describe class usage
 */
class TransformerConfiguration
{
    protected $code;

    protected $service;

    protected $mapping;

    /**
     * TransformerConfiguration constructor.
     *
     * @param string $code
     * @param array  $config
     */
    public function __construct(string $code, array $config)
    {
        $this->code = $code;
        $this->service = $config['service'];
        $this->mapping = $config['mapping'];
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return mixed
     */
    public function getMapping()
    {
        return $this->mapping;
    }

}
