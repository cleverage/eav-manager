<?php

namespace CleverAge\EAVManager\ImportBundle\Process;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;

/**
 * @TODO describe class usage
 */
class CsvWriterProcess implements ProcessInterface
{

    /** @var string */
    protected $filePath;

    /** @var array */
    protected $dataToProceed;

    /**
     * CsvWriterProcess constructor.
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        $this->dataToProceed = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $handler = fopen($this->filePath, 'w+');
        $headers = array_merge(...$this->dataToProceed); // Keep the headers as keys
        $headers = array_fill_keys(array_keys($headers), null); // Unset useless values
        $csvData = [];

        // Check input data
        foreach ($this->dataToProceed as $item) {
            $csvData[] = array_filter(
                $item,
                function ($value) {
                    // For now, only output strings
                    return is_string($value);
                }
            );
        }

        // Write CSV
        fputcsv($handler, array_keys($headers));
        foreach ($csvData as $csvRow) {
            $dataRow = [];
            foreach (array_keys($headers) as $header) {
                $dataRow[] = $csvRow[$header] ?? '';
            }
            fputcsv($handler, $dataRow);
        }
        fclose($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        // TODO: Implement getOutput() method.
    }

}
