<?php

namespace CleverAge\EAVManager\ProcessBundle\Model;

use ProcessBundle\Model\ProcessState;

/**
 * Must be implemented by tasks services
 * The service can read the input value from ProcessState and write it's output to it
 *
 * @see ProcessState for more informations about available actions
 */
interface TaskInterface
{
    /**
     * @param ProcessState $processState
     */
    public function execute(ProcessState $processState);
}
