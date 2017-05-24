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

    /** @var bool */
    protected $forceEnclosures;

    /** @var array */
    protected $dataToProceed;

    /**
     * CsvWriterProcess constructor.
     *
     * @param string $filePath
     * @param bool   $forceEnclosures
     */
    public function __construct($filePath, bool $forceEnclosures = false)
    {
        $this->filePath = $filePath;
        $this->forceEnclosures = $forceEnclosures;
    }

    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        if (!count($data)) {
            throw new \Exception('There is no data to export in a CSV file');
        }

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

        // Check input data (only strings are accepted)
        foreach ($this->dataToProceed as $item) {
            $csvData[] = array_filter(
                $item,
                function ($value) {
                    return is_string($value);
                }
            );
        }

        // Write CSV
        $this->fputcsv($handler, array_keys($headers), ';', '"');
        foreach ($csvData as $csvRow) {
            $dataRow = [];
            foreach (array_keys($headers) as $header) {
                $dataRow[] = $csvRow[$header] ?? '';
            }
            $this->fputcsv($handler, $dataRow, ';', '"', $this->forceEnclosures);
        }
        fclose($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->filePath;
    }

    /**
     * Replicate php's native fputcsv to allow overrides
     *
     * @see http://php.net/manual/en/function.fputcsv.php#77866
     *
     * @param resource $handle
     * @param array    $fields
     * @param string   $delimiter
     * @param string   $enclosure
     * @param bool     $forceEnclosures
     *
     * @return bool|int
     */
    public function fputcsv(&$handle, array $fields = [], $delimiter = ',', $enclosure = '"', $forceEnclosures = false)
    {
        $str = '';
        $escapeChar = '\\';
        /** @var string $value */
        foreach ($fields as $value) {
            if ($forceEnclosures ||
                strpos($value, $delimiter) !== false ||
                strpos($value, $enclosure) !== false ||
                strpos($value, "\n") !== false ||
                strpos($value, "\r") !== false ||
                strpos($value, "\t") !== false ||
                strpos($value, ' ') !== false
            ) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i = 0; $i < $len; $i++) {
                    if ($value[$i] == $escapeChar) {
                        $escaped = 1;
                    } else {
                        if (!$escaped && $value[$i] == $enclosure) {
                            $str2 .= $enclosure;
                        } else {
                            $escaped = 0;
                        }
                    }
                    $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2.$delimiter;
            } else {
                $str .= $value.$delimiter;
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";

        return fwrite($handle, $str);
    }

}
