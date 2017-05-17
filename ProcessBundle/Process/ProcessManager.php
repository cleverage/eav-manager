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
        $processGroups = [];
        $currentGroup = [];

        // Split the process every time there is a blocking process
        foreach ($subprocesses as $subprocess) {
            $currentGroup[] = $subprocess;
            if ($subprocess instanceof BlockingProcessInterface) {
                $processGroups[] = $currentGroup;
                $currentGroup = [];
            }
        }

        // This is the case where the last process is not blocking
        if (count($currentGroup)) {
            $processGroups[] = $currentGroup;
        }

        // Execute groups one by one
        $input = null;
        foreach ($processGroups as $processGroup) {
            $this->runStreamableProcess($processGroup, $input);
            $lastProcess = array_pop($processGroup);
            if ($lastProcess instanceof BlockingProcessInterface) {
                $lastProcess->finalize();
            }
            $input = $lastProcess->getOutput();
        }
    }

    /**
     * @param ProcessInterface[] $processes
     * @param mixed              $input
     */
    protected function runStreamableProcess(array $processes, $input = null)
    {
        if (!count($processes)) {
            return;
        }

        $firstProcess = array_shift($processes);
        $firstProcess->setInput($input);

        do {
            $firstProcess->execute();
            $this->runStreamableProcess($processes, $firstProcess->getOutput());
        } while ($firstProcess instanceof StreamableProcessInterface && !$firstProcess->isOver());
    }
}
