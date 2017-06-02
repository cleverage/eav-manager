<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ImportBundle\Model\CsvFile;
use CleverAge\EAVManager\ProcessBundle\Model\FinalizableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use CleverAge\EAVManager\ProcessBundle\Model\AbstractConfigurableTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
* Reads the file path from configuration and iterates over it
* Ignores any input
*/
abstract class AbstractCsvTask extends AbstractConfigurableTask implements FinalizableTaskInterface
{
    /** @var CsvFile */
    protected $csv;

    /**
     * @param ProcessState $processState
     */
    public function finalize(ProcessState $processState)
    {
        if ($this->csv instanceof CsvFile) {
            $this->csv->close();
        }
    }

    /**
     * @param array $options
     *
     * @throws \UnexpectedValueException
     *
     * @return CsvFile
     */
    protected function initFile(array $options): CsvFile
    {
        if (!$this->csv) {
            $this->csv = new CsvFile(
                $options['file_path'],
                $options['delimiter'],
                $options['enclosure'],
                $options['escape'],
                $options['headers'],
                $options['mode']
            );
        }

        return $this->csv;
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'file_path',
            ]
        );
        $resolver->setAllowedTypes('file_path', ['string']);
        $resolver->setDefaults(
            [
                'delimiter' => ';',
                'enclosure' => '"',
                'escape' => '\\',
                'headers' => null,
                'mode' => 'r',
            ]
        );
        $resolver->setAllowedTypes('delimiter', ['string']);
        $resolver->setAllowedTypes('enclosure', ['string']);
        $resolver->setAllowedTypes('escape', ['string']);
        $resolver->setAllowedTypes('headers', ['NULL', 'array']);
        $resolver->setAllowedTypes('mode', ['string']);
    }
}
