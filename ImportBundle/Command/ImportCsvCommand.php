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
 * Import data from CSV file
 */
class ImportCsvCommand extends ContainerAwareCommand
{
    /** @var ImportConfig */
    protected $importConfig;

    /** @var EAVDataImporter */
    protected $eavDataImporter;

    /** @var ImportContext */
    protected $importContext;

    /** @var array */
    protected $dataBatch;

    /** @var FamilyInterface */
    protected $family;

    protected function configure()
    {
        $this
            ->setName('eavmanager:import:csv')
            ->addArgument('config', InputArgument::REQUIRED, 'Configuration code to use for mapping and options')
            ->setDescription('Import CSV');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Doctrine Optimization
        $this->getContainer()->get('doctrine')->getConnection()->getConfiguration()->setSQLLogger(null);

        // Disable Doctrine's event listener for publishing
        $this->getContainer()->get('sidus_eav_publishing.doctrine_orm.subscriber')->setDisabled(true);

        $this->importConfig = $this->getContainer()->get('eavmanager.import_configuration.handler')
            ->getImport($input->getArgument('config'));

        $filePath = $this->importConfig->getFilePath();
        if (!file_exists($filePath)) {
            throw new \UnexpectedValueException("File not found {$filePath}");
        }

        $this->eavDataImporter = $this->importConfig->getService();
        $this->eavDataImporter->setOutput($output);
        $this->importContext = $this->eavDataImporter->getImportContext();

        if ($filePath && $this->importContext->hasProcessedFile($filePath)) {
            throw new \LogicException('File already processed but import context was never closed properly ?');
        }

        $this->family = $this->importConfig->getFamily();

        $this->processFile($filePath, $output);

        if ($filePath) {
            $this->importContext->addProcessedFile($filePath);
        }

        // Close import and archive context
        $this->eavDataImporter->terminate();

        return 0;
    }

    /**
     * @param $data
     * @return array
     * @throws \UnexpectedValueException
     */
    protected function mapValues($data)
    {
        $mappedData = [];
        foreach ($this->importConfig->getMapping() as $attributeCode => $config) {
            $attribute = $this->importConfig->getFamily()->getAttribute($attributeCode);
            $importCode = $attributeCode;
            if (isset($config['code'])) {
                $importCode = $config['code'];
            }
            if (!array_key_exists($importCode, $data)) {
                throw new \UnexpectedValueException("Missing column {$importCode} for attribute mapping {$attributeCode}");
            }
            $value = $data[$importCode];
            if ($attribute->isMultiple()) {
                if (!isset($config['splitCharacter'])) {
                    throw new \UnexpectedValueException("Multiple attribute '{$attributeCode}' expects a splitCharacter option in import mapping");
                }
                $value = explode($config['splitCharacter'], $value);
            }
            $mappedData[$attributeCode] = $value;
        }

        return $mappedData;
    }

    /**
     * @param string          $filePath
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function processFile($filePath, OutputInterface $output)
    {
        $csv = new CsvFile($filePath, ';');

        $output->writeln("<info>Importing family {$this->family->getCode()}</info>");
        $progress = new ProgressBar($output, $csv->getLineCount());
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $line = 0;
        while (!$csv->isEndOfFile()) {
            $line++;
            $this->readLine($csv, $progress);
        }

        // Save remaining data
        if (count($this->dataBatch)) {
            $this->eavDataImporter->loadBatch($this->family, $this->dataBatch, $progress);
        }

        $progress->finish();
        $output->writeln('');
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

        $data = $this->mapValues($rawData);
        $reference = $data[$this->family->getAttributeAsIdentifier()->getCode()];
        $this->dataBatch[$reference] = $data;

        if (count($this->dataBatch) >= $this->importContext->getBatchCount()) {
            $this->eavDataImporter->loadBatch($this->family, $this->dataBatch, $progress);
            $this->dataBatch = [];
        }
    }
}
