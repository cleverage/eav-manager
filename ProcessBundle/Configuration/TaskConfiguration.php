<?php

namespace CleverAge\EAVManager\ProcessBundle\Configuration;

use CleverAge\EAVManager\ProcessBundle\Model\TaskInterface;

/**
 * Represents a task configuration inside a process
 */
class TaskConfiguration
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $serviceReference;

    /** @var TaskInterface */
    protected $task;

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $inputs = [];

    /**
     * @param string $code
     * @param string $serviceReference
     * @param array  $options
     * @param array  $inputs
     */
    public function __construct($code, $serviceReference, array $options, array $inputs)
    {
        $this->code = $code;
        $this->serviceReference = $serviceReference;
        $this->options = $options;
        $this->inputs = $inputs;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getServiceReference(): string
    {
        return $this->serviceReference;
    }

    /**
     * @return TaskInterface
     */
    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    /**
     * @param TaskInterface $task
     */
    public function setTask(TaskInterface $task)
    {
        $this->task = $task;
    }

    /**
     * @return array
     */
    public function getOptions(): array
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
    public function getInputs(): array
    {
        return $this->inputs;
    }
}
