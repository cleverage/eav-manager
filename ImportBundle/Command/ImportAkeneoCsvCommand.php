<?php

namespace CleverAge\EAVManager\ImportBundle\Command;

use CleverAge\EAVManager\ImportBundle\DataTransfer\ImportContext;
use CleverAge\EAVManager\ImportBundle\Import\EAVDataImporter;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CleverAge\EAVManager\ImportBundle\Model\CsvFile;
use CleverAge\EAVManager\ImportBundle\Model\ImportConfig;

/**
 * Import data from CSV Akeneo file with all families mixed up
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

        $data = $this->mapValues($rawData);
        $reference = $data[$this->family->getAttributeAsIdentifier()->getCode()];
        $this->dataBatch[$reference] = $data;

        if (count($this->dataBatch) >= $this->importContext->getBatchCount()) {
            $this->eavDataImporter->loadBatch($this->family, $this->dataBatch, $progress);
            $this->dataBatch = [];
        }
    }
}
