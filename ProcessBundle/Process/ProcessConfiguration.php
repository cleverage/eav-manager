<?php

namespace CleverAge\EAVManager\ProcessBundle\Process;

/**
 * @TODO describe class usage
 */
class ProcessConfiguration
{
    /** @var string */
    protected $code;

    protected $processManagerService;

    /** @var ProcessInterface[] */
    protected $subprocess;

    /**
     * @param string $code
     * @param array  $configuration
     */
    public function __construct(string $code, array $configuration)
    {
        $this->code = $code;
        $this->processManagerService = $configuration['service'];

        //TODO check interfaces
        $this->subprocess = $configuration['subprocess'];
    }

    /**
     * @return ProcessManager|object
     */
    public function getProcessManagerService()
    {
        return $this->processManagerService;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getSubprocess()
    {
        return $this->subprocess;
    }
}
