<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use CleverAge\EAVManager\ProcessBundle\Model\TaskInterface;

/**
 * Dump the content of the input
 */
class DebugTask implements TaskInterface
{
    /**
     * @param ProcessState $processState
     */
    public function execute(ProcessState $processState)
    {
        dump($processState->getInput()); // Dump input
    }
}
