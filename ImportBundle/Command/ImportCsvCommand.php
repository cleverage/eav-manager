<?php

namespace CleverAge\EAVManager\ImportBundle\Command;

use CleverAge\EAVManager\ImportBundle\DataTransfer\ImportContext;
use CleverAge\EAVManager\ImportBundle\Import\EAVDataImporter;
use CleverAge\EAVManager\ImportBundle\Model\CsvFile;
use CleverAge\EAVManager\ImportBundle\Model\ImportConfig;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

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
     *
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
        $this->importContext->setBatchCount($this->importConfig->getOption('batch_count', 30));

        $this->family = $this->importConfig->getFamily();
        if (!$this->family->getAttributeAsIdentifier()) {
            $m = "Cannot import data for family {$this->family->getCode()} without an attributeAsIdentifier";
            throw new \LogicException($m);
        }

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
     *
     * @return array
     * @throws TransformationFailedException
     * @throws \LogicException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \UnexpectedValueException
     */
    protected function mapValues($data)
    {
        $mappedData = [];
        foreach ($this->importConfig->getMapping() as $attributeCode => $config) {
            $attribute = $this->importConfig->getFamily()->getAttribute($attributeCode);
            $importCode = $attributeCode;

            // Custom code for mapping
            if (isset($config['code'])) {
                $importCode = $config['code'];
            }
            if (!array_key_exists($importCode, $data)) {
                $m = "Missing column '{$importCode}' in CVS for attribute mapping {$attributeCode}";
                throw new \UnexpectedValueException($m);
            }

            // Fetching value
            $value = $data[$importCode];

            // Case multiple @todo: Maybe this should be handled by a transformer ?
            if ($attribute->isMultiple()) {
                if (!isset($config['splitCharacter'])) {
                    $m = "Multiple attribute '{$attributeCode}' expects a splitCharacter option in import mapping";
                    throw new \UnexpectedValueException($m);
                }
                if (null === $value || '' === $value) { // Skip case where value is empty
                    $value = [];
                } else {
                    $value = explode($config['splitCharacter'], $value);
                }
            }

            $value = $this->transformValue($attribute, $value, $config);

            $mappedData[$attributeCode] = $value;
        }

        return $mappedData;
    }

    /**
     * @param string          $filePath
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function processFile($filePath, OutputInterface $output)
    {
        $delimiter = $this->importConfig->getOption('delimiter', ';');
        $enclosure = $this->importConfig->getOption('enclosure', '"');
        $escape = $this->importConfig->getOption('escape', '\\');
        $headers = $this->importConfig->getOption('headers');
        $csv = new CsvFile($filePath, $delimiter, $enclosure, $escape, $headers);

        $output->writeln("<info>Importing family {$this->family->getCode()}</info>");
        $progress = new ProgressBar($output, $csv->getLineCount());
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $currentPosition = $this->importContext->getCurrentPosition();
        if (is_array($currentPosition) && array_key_exists('seek', $currentPosition) &&
            array_key_exists('progress', $currentPosition)
        ) {
            $csv->seek($currentPosition['seek']);
            $progress->setProgress($currentPosition['progress']);
        }

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

        $data = $this->mapValues($rawData);
        $reference = $data[$this->family->getAttributeAsIdentifier()->getCode()];
        $this->dataBatch[$reference] = $data;

        if (count($this->dataBatch) >= $this->importContext->getBatchCount()) {
            $this->eavDataImporter->loadBatch($this->family, $this->dataBatch, $progress);
            $this->importContext->setCurrentPosition([
                'seek' => $csv->tell(),
                'progress' => $progress->getProgress(),
            ]);
            $this->eavDataImporter->saveContext(false);
            $this->dataBatch = [];
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $value
     * @param array              $config
     *
     * @throws \LogicException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \UnexpectedValueException
     *
     * @return mixed
     */
    protected function transformValue(AttributeInterface $attribute, $value, array $config = null)
    {
        $transformer = null;

        // Default DataTransformer for dates
        if (is_string($value) && !is_numeric($value)) {
            if ($attribute->getType()->getDatabaseType() === 'dateValue') {
                $transformer = $this->getContainer()->get('eavmanager.import.transformer.simple_date');
            }
            if ($attribute->getType()->getDatabaseType() === 'datetimeValue') {
                $transformer = $this->getContainer()->get('eavmanager.import.transformer.simple_datetime');
            }
        }

        // Custom transformer in configuration
        if (isset($config['transformer'])) {
            $transformer = $this->getContainer()->get(ltrim($config['transformer'], '@'));
            if (!$transformer instanceof DataTransformerInterface) {
                $m = "Transformer for attribute mapping '{$attribute->getCode()}' must be a DataTransformerInterface";
                throw new \UnexpectedValueException($m);
            }
        }

        // @todo handle custom EAV transformer where we pass the attribute and the config ?

        if ($transformer) {
            $value = $transformer->reverseTransform($value);
        } elseif ($value === '\\N') { // MySQL CSV outputs \N for null values
            return null;
        }

        return $value;
    }
}
