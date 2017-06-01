<?php

namespace CleverAge\EAVManager\ProcessBundle\DataSource;

use CleverAge\EAVManager\ImportBundle\Model\CsvFile;
use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;

/**
 * Provide data from a CSV file
 * TODO : implement StreamableProcessInterface
 */
class CsvSourceProcess implements ProcessInterface
{

    /**
     * Can be provided from input or from constructor
     *
     * @var string
     */
    protected $filePath;

    /** @var string */
    protected $idColumn;

    /** @var array */
    protected $options;

    /** @var array */
    protected $parsedData;

    /**
     * CsvSource constructor.
     *
     * @param string $filePath
     * @param string $idColumn
     * @param array  $options
     */
    public function __construct($filePath, $idColumn, array $options = [])
    {
        $this->filePath = $filePath;
        $this->idColumn = $idColumn;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        if ($data) {
            $this->filePath = $data;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->parsedData = $this->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->parsedData;
    }


    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    protected function getData(): array
    {
        $data = [];
        $csvFile = $this->getFile($this->filePath);
        while (!$csvFile->isEndOfFile()) {
            $row = $csvFile->readLine();

            // Ignore empty lines
            if ($row) {
                if (!array_key_exists($this->idColumn, $row)) {
                    throw new \InvalidArgumentException(
                        "The column {$this->idColumn} does not exists inside file {$this->filePath}"
                    );
                }

                $data[$row[$this->idColumn]] = $row;
            }
        }

        return $data;
    }

    /**
     * @param string $filePath
     *
     * @throws \Exception
     *
     * @return CsvFile
     */
    protected function getFile($filePath): CsvFile
    {
        $delimiter = $this->options['delimiter'] ?? ';';
        $enclosure = $this->options['enclosure'] ?? '"';
        $escape = $this->options['escape'] ?? '\\';

        return new CsvFile($filePath, $delimiter, $enclosure, $escape);
    }

}
