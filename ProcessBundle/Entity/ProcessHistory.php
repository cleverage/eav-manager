<?php

namespace ProcessBundle\Entity;

use CleverAge\EAVManager\ProcessBundle\Configuration\ProcessConfiguration;

/**
 *
 */
class ProcessHistory
{
    const STATE_STARTED = 'started';
    const STATE_SUCCESS = 'success';
    const STATE_FAILED = 'failed';

    /**
     * @var string
     */
    protected $processCode;

    /**
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @var string
     */
    protected $state;

    /**
     * @param ProcessConfiguration $processConfiguration
     */
    public function __construct(ProcessConfiguration $processConfiguration)
    {
        $this->processCode = $processConfiguration->getCode();
    }

    /**
     * @return string
     */
    public function getProcessCode(): string
    {
        return $this->processCode;
    }

    /**
     * @param string $processCode
     */
    public function setProcessCode(string $processCode)
    {
        $this->processCode = $processCode;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state)
    {
        $this->state = $state;
    }
    
}
