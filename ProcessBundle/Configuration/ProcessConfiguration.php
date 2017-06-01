<?php

namespace CleverAge\EAVManager\ProcessBundle\Configuration;

use CleverAge\EAVManager\ProcessBundle\Exception\MissingTaskConfigurationException;

/**
 * Holds the processes configuration to launch a task
 */
class ProcessConfiguration
{
    /** @var string */
    protected $code;

    /** @var array */
    protected $options = [];

    /** @var TaskConfiguration */
    protected $entryPoint;

    /** @var TaskConfiguration[] */
    protected $taskConfigurations;

    /**
     * @param string              $code
     * @param array               $options
     * @param string              $entryPoint
     * @param TaskConfiguration[] $taskConfigurations
     */
    public function __construct($code, array $taskConfigurations, array $options = [], $entryPoint = null)
    {
        $this->code = $code;
        $this->taskConfigurations = $taskConfigurations;
        $this->options = $options;
        $this->entryPoint = $entryPoint;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @throws \CleverAge\EAVManager\ProcessBundle\Exception\MissingTaskConfigurationException
     *
     * @return TaskConfiguration
     */
    public function getEntryPoint(): TaskConfiguration
    {
        if (null === $this->entryPoint) {
            return reset($this->taskConfigurations);
        }

        return $this->getTaskConfiguration($this->entryPoint);
    }

    /**
     * @return TaskConfiguration[]
     */
    public function getTaskConfigurations(): array
    {
        return $this->taskConfigurations;
    }

    /**
     * @param string $taskCode
     *
     * @throws \CleverAge\EAVManager\ProcessBundle\Exception\MissingTaskConfigurationException
     *
     * @return TaskConfiguration
     */
    public function getTaskConfiguration(string $taskCode): TaskConfiguration
    {
        if (!array_key_exists($taskCode, $this->taskConfigurations)) {
            throw new MissingTaskConfigurationException($taskCode);
        }

        return $this->taskConfigurations[$taskCode];
    }

    /**
     * @param TaskConfiguration $currentTaskConfiguration
     *
     * @return TaskConfiguration[]
     */
    public function getNextTasks(TaskConfiguration $currentTaskConfiguration)
    {
        $taskConfigurations = [];
        foreach ($this->taskConfigurations as $taskConfiguration) {
            if (in_array($currentTaskConfiguration->getCode(), $taskConfiguration->getInputs(), true)) {
                $taskConfigurations[] = $taskConfiguration;
            }
        }

        return $taskConfigurations;
    }
}
