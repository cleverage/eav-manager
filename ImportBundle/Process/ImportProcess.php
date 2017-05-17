<?php

namespace CleverAge\EAVManager\ImportBundle\Process;

use CleverAge\EAVManager\ImportBundle\Configuration\ImportConfigurationHandler;
use CleverAge\EAVManager\ImportBundle\Entity\ImportHistory;
use CleverAge\EAVManager\ImportBundle\Model\ImportConfig;
use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;

/**
 * @TODO describes class usage
 */
class ImportProcess implements ProcessInterface
{
    /** @var ImportConfigurationHandler */
    protected $importConfigurationHandler;

    /** @var ImportConfig|ImportConfig[] */
    protected $importConfig;

    /** @var ImportHistory|ImportHistory[] */
    protected $importHistory;

    /**
     * @param ImportConfigurationHandler $importConfigurationHandler
     */
    public function __construct(ImportConfigurationHandler $importConfigurationHandler)
    {
        $this->importConfigurationHandler = $importConfigurationHandler;
    }

    /**
     * @param array|string $data Import code to process
     *
     * @throws \UnexpectedValueException
     */
    public function setInput($data)
    {
        $this->importConfig = is_array($data) ?
            array_map([$this->importConfigurationHandler, 'getImport'], $data) :
            $this->importConfigurationHandler->getImport($data);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->importHistory = is_array($this->importConfig) ?
            array_map([$this, 'runImport'], $this->importConfig) :
            $this->runImport($this->importConfig);
    }

    /**
     * @return ImportHistory|ImportHistory[]
     */
    public function getOutput()
    {
        return $this->importHistory;
    }

    /**
     * Run an import configuration.
     *
     * @param ImportConfig $importConfig
     *
     * @return ImportHistory
     */
    protected function runImport(ImportConfig $importConfig)
    {
        $eavImporter = $importConfig->getService();

        return $eavImporter->import($importConfig);
    }
}
