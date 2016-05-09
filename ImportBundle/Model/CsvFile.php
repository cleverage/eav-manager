<?php

namespace CleverAge\EAVManager\ImportBundle\Model;

/**
 * Read CSV
 */
class CsvFile
{
    /** @var string */
    protected $filePath;

    /** @var string */
    protected $delimiter;

    /** @var string */
    protected $enclosure;

    /** @var string */
    protected $escape;

    /** @var resource */
    protected $handler;

    /** @var int */
    protected $lineCount;

    /** @var array */
    protected $headers;

    /** @var int */
    protected $headerCount;

    /** @var int */
    protected $currentLine = 0;

    /**
     * @param string $filePath
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     */
    public function __construct($filePath, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;

        $this->handler = fopen($filePath, 'r');
        $this->headers = fgetcsv($this->handler, null, $delimiter, $enclosure, $escape);
        $this->headerCount = count($this->headers);
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * @return string
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * @return resource
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function getLineCount()
    {
        if (null === $this->lineCount) {
            $line = 0;
            while (!$this->isEndOfFile()) {
                $line++;
                $this->readRaw();
            }
            $this->rewind();

            $this->lineCount = $line;
        }

        return $this->lineCount;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getHeaderCount()
    {
        return $this->headerCount;
    }

    /**
     * @return int
     */
    public function getCurrentLine()
    {
        return $this->currentLine;
    }

    /**
     * @return bool
     */
    public function isEndOfFile()
    {
        return feof($this->handler);
    }

    /**
     * @param null|int $length
     * @return array
     */
    public function readRaw($length = null)
    {
        $this->currentLine++;

        return fgetcsv($this->handler, $length, $this->delimiter, $this->enclosure, $this->escape);
    }

    /**
     * @param null $length
     * @throws \UnexpectedValueException
     * @return array|null
     */
    public function readLine($length = null)
    {
        $values = $this->readRaw($length);

        if (count($values) !== $this->headerCount) {
            if ($this->isEndOfFile()) {
                return null;
            }
            $message = "Number of columns not matching on line {$this->currentLine} for file {$this->filePath}";
            throw new \UnexpectedValueException($message);
        }

        return array_combine($this->headers, $values);
    }

    /**
     * @return bool
     * @throws \RuntimeException
     */
    public function rewind()
    {
        if (!rewind($this->handler)) {
            throw new \RuntimeException('Unable to rewind CSV resource file');
        }
        $this->currentLine = 0;
        $this->readRaw(); // skip headers
    }
}
