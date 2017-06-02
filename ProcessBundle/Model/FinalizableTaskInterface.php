<?php

namespace CleverAge\EAVManager\ProcessBundle\Model;

use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;

/**
 * Allow the task to be initialized before any execution is done
 */
interface FinalizableTaskInterface extends TaskInterface
{
    /**
     * @param ProcessState $processState
     */
    public function finalize(ProcessState $processState);
}
