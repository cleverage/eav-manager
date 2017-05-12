<?php

namespace CleverAge\EAVManager\ProcessBundle\Process;

/**
 * @TODO describe class usage
 */
class ProcessManager
{
    /**
     * @param ProcessInterface[] $subprocesses
     */
    public function execute(array $subprocesses)
    {
        $input = null;
        foreach ($subprocesses as $subprocess) {
            $subprocess->setInput($input);
            $subprocess->execute();
            $input = $subprocess->getOutput();
        }
    }
}
