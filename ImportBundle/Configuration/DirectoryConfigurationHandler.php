<?php

namespace CleverAge\EAVManager\ImportBundle\Configuration;

/**
 * Holds the configuration of the different directories used for the import and exports
 */
class DirectoryConfigurationHandler
{
    /** @var string */
    protected $baseDirectory;

    /** @var string */
    protected $downloadsDirectory;

    /** @var string */
    protected $archiveDirectory;

    /**
     * DirectoryConfigurationHandler constructor.
     *
     * @param string $baseDirectory
     * @param string $downloadsDirectory
     *
     * @throws \RuntimeException
     */
    public function __construct($baseDirectory, $downloadsDirectory)
    {
        $this->baseDirectory = rtrim($baseDirectory, '/');
        $this->downloadsDirectory = rtrim($downloadsDirectory, '/');
        $this->archiveDirectory = $this->baseDirectory.'/archives';

        if (@mkdir($this->baseDirectory) && !is_dir($this->baseDirectory)) {
            throw new \RuntimeException("Unable to create directory {$this->baseDirectory}");
        }
        if (@mkdir($this->archiveDirectory) && !is_dir($this->archiveDirectory)) {
            throw new \RuntimeException("Unable to create directory {$this->archiveDirectory}");
        }
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @return string
     */
    public function getDownloadsDirectory()
    {
        return $this->downloadsDirectory;
    }

    /**
     * @return string
     */
    public function getArchiveDirectory()
    {
        return $this->archiveDirectory;
    }
}
