<?php

namespace CleverAge\EAVManager\ProcessBundle\Model;

use CleverAge\EAVManager\ProcessBundle\Configuration\ProcessConfiguration;
use CleverAge\EAVManager\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\EAVManager\ProcessBundle\Entity\ProcessHistory;
use CleverAge\EAVManager\ProcessBundle\Entity\TaskHistory;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Used to pass information between tasks
 */
class ProcessState
{
    /** @var ProcessConfiguration */
    protected $processConfiguration;

    /** @var ProcessHistory */
    protected $processHistory;

    /** @var TaskConfiguration */
    protected $taskConfiguration;

    /** @var mixed */
    protected $input;

    /** @var mixed */
    protected $output;

    /** @var TaskHistory[] */
    protected $taskHistories = [];

    /** @var bool */
    protected $started = false;

    /** @var bool */
    protected $stopped = false;

    /** @var \Exception */
    protected $exception;

    /** @var OutputInterface */
    protected $consoleOutput;

    /** @var int */
    protected $returnCode;

    /**
     * @param ProcessConfiguration $processConfiguration
     * @param ProcessHistory       $processHistory
     */
    public function __construct(ProcessConfiguration $processConfiguration, ProcessHistory $processHistory)
    {
        $this->processConfiguration = $processConfiguration;
        $this->processHistory = $processHistory;
    }

    /**
     * @return ProcessConfiguration
     */
    public function getProcessConfiguration()
    {
        return $this->processConfiguration;
    }

    /**
     * @return ProcessHistory
     */
    public function getProcessHistory()
    {
        return $this->processHistory;
    }

    /**
     * @return TaskConfiguration
     */
    public function getTaskConfiguration(): TaskConfiguration
    {
        return $this->taskConfiguration;
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     */
    public function setTaskConfiguration(TaskConfiguration $taskConfiguration)
    {
        $this->taskConfiguration = $taskConfiguration;
    }

    /**
     * @param string $message
     * @param string $level
     * @param string $reference
     * @param array  $context
     */
    public function log(string $message, string $level = LogLevel::ERROR, string $reference = null, array $context = [])
    {
        $taskHistory = new TaskHistory($this->getProcessHistory(), $this->getTaskConfiguration());
        $taskHistory->setMessage($message);
        $taskHistory->setLevel($level);
        $taskHistory->setReference($reference);
        $taskHistory->setContext($context);

        $this->taskHistories[] = $taskHistory;
    }

    /**
     * @return TaskHistory[]
     */
    public function getTaskHistories(): array
    {
        return $this->taskHistories;
    }

    /**
     * Cleanup log
     */
    public function clearTaskHistories()
    {
        $this->taskHistories = [];
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return boolean
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @param boolean $started
     */
    public function setStarted(bool $started)
    {
        $this->started = $started;
    }

    /**
     * @param \Exception $e
     */
    public function stop(\Exception $e = null)
    {
        $this->setException($e);
        $this->setStopped(true);
    }

    /**
     * @return boolean
     */
    public function isStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * @param boolean $stopped
     */
    public function setStopped(bool $stopped)
    {
        $this->stopped = $stopped;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return OutputInterface
     */
    public function getConsoleOutput()
    {
        return $this->consoleOutput;
    }

    /**
     * @param OutputInterface $consoleOutput
     */
    public function setConsoleOutput(OutputInterface $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }

    /**
     * @return int
     */
    public function getReturnCode()
    {
        if (null !== $this->returnCode) {
            return $this->returnCode;
        }

        return 0;
    }

    /**
     * @param int $returnCode
     */
    public function setReturnCode(int $returnCode)
    {
        $this->returnCode = $returnCode;
    }
}
