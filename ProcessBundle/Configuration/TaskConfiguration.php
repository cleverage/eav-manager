<?php

namespace CleverAge\EAVManager\ProcessBundle\Configuration;

/**
 * Represents a task configuration inside a process
 */
class TaskConfiguration
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $service;

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $inputs = [];

    /**
     * @param string $code
     * @param string $service
     * @param array  $options
     * @param array  $inputs
     */
    public function __construct($code, $service, array $options, array $inputs)
    {
        $this->code = $code;
        $this->service = $service;
        $this->options = $options;
        $this->inputs = $inputs;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $code
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($code, $default = null)
    {
        if (array_key_exists($code, $this->options)) {
            return $this->options[$code];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function getInputs()
    {
        return $this->inputs;
    }
}
