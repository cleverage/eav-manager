<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ImportBundle\Model\CsvFile;
use CleverAge\EAVManager\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Reads the file path from configuration and iterates over it
 * Ignores any input
 */
class CsvReaderTask extends AbstractCsvTask implements IterableTaskInterface
{
    /**
     * @param ProcessState $processState
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function execute(ProcessState $processState)
    {
        $options = $this->getOptions($processState);
        try {
            if (!$this->csv instanceof CsvFile) {
                $this->initFile($options);
            }
            $processState->setOutput($this->csv->readLine());
        } catch (\Exception $e) {
            $processState->stop($e);
        }
    }

    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration
     *
     * @param ProcessState $processState
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function next(ProcessState $processState)
    {
        if (!$this->csv instanceof CsvFile) {
            throw new \LogicException('No CSV File initialized');
        }

        return !$this->csv->isEndOfFile();
    }
}
