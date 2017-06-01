<?php

namespace ProcessBundle\Entity;

use CleverAge\EAVManager\ProcessBundle\Configuration\TaskConfiguration;

/**
 * History element for a task
 */
class TaskHistory
{
    /**
     * @var ProcessHistory
     */
    protected $processHistory;

    /**
     * @var string
     */
    protected $taskCode;

    /**
     * @var \DateTime
     */
    protected $loggedAt;

    /**
     * @var int
     */
    protected $level = E_NOTICE;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var string
     */
    protected $message;

    /**
     * TaskHistory constructor.
     *
     * @param ProcessHistory    $processHistory
     * @param TaskConfiguration $taskConfiguration
     */
    public function __construct(ProcessHistory $processHistory, TaskConfiguration $taskConfiguration)
    {
        $this->processHistory = $processHistory;
        $this->taskCode = $taskConfiguration->getCode();
        $this->setLoggedAt(new \DateTime());
    }


    /**
     * @return ProcessHistory
     */
    public function getProcessHistory(): ProcessHistory
    {
        return $this->processHistory;
    }

    /**
     * @param ProcessHistory $processHistory
     */
    public function setProcessHistory(ProcessHistory $processHistory)
    {
        $this->processHistory = $processHistory;
    }

    /**
     * @return string
     */
    public function getTaskCode(): string
    {
        return $this->taskCode;
    }

    /**
     * @param string $taskCode
     */
    public function setTaskCode(string $taskCode)
    {
        $this->taskCode = $taskCode;
    }

    /**
     * @return \DateTime
     */
    public function getLoggedAt(): \DateTime
    {
        return $this->loggedAt;
    }

    /**
     * @param \DateTime $loggedAt
     */
    public function setLoggedAt(\DateTime $loggedAt)
    {
        $this->loggedAt = $loggedAt;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference(string $reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }
}
