<?php

namespace CleverAge\EAVManager\ImportBundle\Configuration;

use UnexpectedValueException;
use CleverAge\EAVManager\ImportBundle\Model\ImportConfig;

/**
 * Container for all import configurations
 */
class ImportConfigurationHandler
{
    /** @var ImportConfig[] */
    protected $imports;

    /**
     * @param ImportConfig $import
     */
    public function addImport(ImportConfig $import)
    {
        $this->imports[$import->getCode()] = $import;
    }

    /**
     * @return ImportConfig[]
     */
    public function getImports()
    {
        return $this->imports;
    }

    /**
     * @param string $code
     *
     * @return ImportConfig
     * @throws UnexpectedValueException
     */
    public function getImport($code)
    {
        if (empty($this->imports[$code])) {
            throw new UnexpectedValueException("No import with code : {$code}");
        }

        return $this->imports[$code];
    }
}
