<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ProcessBundle\Model\FinalizableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use Psr\Log\LogLevel;

class StatCounterTask implements FinalizableTaskInterface
{
    /** @var int */
    protected $counter = 0;

    /**
     * @param ProcessState $processState
     */
    public function finalize(ProcessState $processState)
    {
        $flatInputs = implode(', ', $processState->getTaskConfiguration()->getInputs());
        $processState->log("Processed item count: {$this->counter}", LogLevel::INFO, $flatInputs);
    }

    /**
     * @param ProcessState $processState
     */
    public function execute(ProcessState $processState)
    {
        $this->counter++;
    }
}
