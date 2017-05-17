<?php

namespace CleverAge\EAVManager\ImportBundle\Source;

use CleverAge\EAVManager\ImportBundle\Model\CsvFile;

/**
 * Provide data from a CSV file.
 */
class CsvSource implements DataSourceInterface
{
    /** @var string */
    protected $filePath;

    /** @var string */
    protected $idColumn;

    /** @var array */
    protected $options;

    /**
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
     *
     * @throws \Exception
     */
    public function getData(): array
    {
        $data = [];
        $csvFile = $this->getFile($this->filePath);
        while (!$csvFile->isEndOfFile()) {
            $row = $csvFile->readLine();
            if (!array_key_exists($this->idColumn, $row)) {
                throw new \InvalidArgumentException(
                    "The column {$this->idColumn} does not exists inside file {$this->filePath}"
                );
            }

            $data[$row[$this->idColumn]] = $row;
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
