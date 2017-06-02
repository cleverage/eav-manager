<?php

namespace CleverAge\EAVManager\ProcessBundle\Registry;

use CleverAge\EAVManager\ProcessBundle\Configuration\ProcessConfiguration;
use CleverAge\EAVManager\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\EAVManager\ProcessBundle\Exception\MissingProcessException;

/**
 * Build and holds all the process configurations
 */
class ProcessConfigurationRegistry
{
    /** @var ProcessConfiguration[] */
    protected $processConfigurations;

    /**
     * @param array $rawConfiguration
     */
    public function __construct(array $rawConfiguration)
    {
        foreach ($rawConfiguration as $processCode => $rawProcessConfiguration) {
            $taskConfigurations = [];
            /** @noinspection ForeachSourceInspection */
            foreach ($rawProcessConfiguration['tasks'] as $taskCode => $rawTaskConfiguration) {
                $taskConfigurations[$taskCode] = new TaskConfiguration(
                    $taskCode,
                    $rawTaskConfiguration['service'],
                    $rawTaskConfiguration['options'],
                    $rawTaskConfiguration['inputs']
                );
            }

            $this->processConfigurations[$processCode] = new ProcessConfiguration(
                $processCode,
                $taskConfigurations,
                $rawProcessConfiguration['options'],
                $rawProcessConfiguration['entry_point']
            );
        }
    }

    /**
     * @param string $processCode
     *
     * @throws \CleverAge\EAVManager\ProcessBundle\Exception\MissingProcessException
     *
     * @return ProcessConfiguration
     */
    public function getProcessConfiguration(string $processCode): ProcessConfiguration
    {
        if (!array_key_exists($processCode, $this->processConfigurations)) {
            throw new MissingProcessException($processCode);
        }

        return $this->processConfigurations[$processCode];
    }

    /**
     * @return ProcessConfiguration[]
     */
    public function getProcessConfigurations(): array
    {
        return $this->processConfigurations;
    }
}
