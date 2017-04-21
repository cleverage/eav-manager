<?php

namespace CleverAge\EAVManager\ImportBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use CleverAge\EAVManager\ImportBundle\Model\CsvFile;

/**
 * Import data from CSV Akeneo file with all families mixed up
 * @deprecated
 */
class ImportAkeneoCsvCommand extends ImportCsvCommand
{
    protected function configure()
    {
        $this
            ->setName('eavmanager:import:akeneo-csv')
            ->addArgument('config', InputArgument::REQUIRED, 'Configuration code to use for mapping and options')
            ->setDescription('Import Akeneo CSV');
    }

    /**
     * @param CsvFile     $csv
     * @param ProgressBar $progress
     *
     * @throws \Exception
     */
    protected function readLine(CsvFile $csv, ProgressBar $progress)
    {
        $rawData = $csv->readLine();
        if (null === $rawData) {
            $progress->advance();

            return;
        }

        $akeneoFamily = $this->importConfig->getOption('akeneo_family');
        if (array_key_exists('family', $rawData)
            && $akeneoFamily
            && $rawData['family'] !== $akeneoFamily
        ) {
            $progress->advance();

            return;
        }

        $this->processLine($csv, $progress, $rawData);
    }
}
