<?php

namespace CleverAge\EAVManager\ProcessBundle\Model;

use ProcessBundle\Model\ProcessState;

/**
 * Allow the task to be initialized before any execution is done
 */
interface InitializableTaskInterface extends TaskInterface
{
    /**
     * @param ProcessState $processState
     */
    public function initialize(ProcessState $processState);
}
