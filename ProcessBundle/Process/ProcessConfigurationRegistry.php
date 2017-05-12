<?php

namespace CleverAge\EAVManager\ProcessBundle\Process;

/**
 * @TODO describe class usage
 */
class ProcessConfigurationRegistry
{
    /** @var ProcessConfiguration[] */
    protected $processConfigurations = [];

    public function addProcessConfiguration(ProcessConfiguration $processConfig)
    {
        $this->processConfigurations[$processConfig->getCode()] = $processConfig;
    }

    /**
     * @return ProcessConfiguration[]
     */
    public function getProcessConfigurations(): array
    {
        return $this->processConfigurations;
    }

    public function getProcessConfiguration(string $code)
    {
        if (!array_key_exists($code, $this->processConfigurations)) {
            throw new \Exception("Process {$code} does not exists");
        }

        return $this->processConfigurations[$code];
    }
}
