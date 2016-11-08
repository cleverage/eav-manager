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

    /** @var bool */
    protected $manualHeaders = false;

    /** @var int */
    protected $headerCount;

    /** @var int */
    protected $currentLine = 0;

    /** @var bool */
    protected $isClosed;

    /**
     * @param string $filePath
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @param array  $headers
     *
     * @throws \UnexpectedValueException
     */
    public function __construct($filePath, $delimiter = ',', $enclosure = '"', $escape = '\\', array $headers = null)
    {
        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;

        $this->handler = fopen($filePath, 'r');
        if (null === $headers) {
            $this->headers = fgetcsv($this->handler, null, $delimiter, $enclosure, $escape);
        } else {
            $this->manualHeaders = true;
            $this->headers = $headers;
        }
        if (false === $this->headers || 0 === count($this->headers)) {
            throw new \UnexpectedValueException("Unable to open file as CSV : {$filePath}");
        }
        $this->headers = array_map('trim', $this->headers); // Trimming headers
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
     * @throws \RuntimeException
     *
     * @return int
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
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function isEndOfFile()
    {
        $this->assertOpened();

        return feof($this->handler);
    }

    /**
     * Warning, this function will return exactly the same value as the fgetcsv() function
     *
     * @param null|int $length
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function readRaw($length = null)
    {
        $this->assertOpened();
        $this->currentLine++;

        return fgetcsv($this->handler, $length, $this->delimiter, $this->enclosure, $this->escape);
    }

    /**
     * @param int|null $length
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return array|null
     */
    public function readLine($length = null)
    {
        $values = $this->readRaw($length);

        if (false === $values) {
            if ($this->isEndOfFile()) {
                return null;
            }
            $message = "Unable to parse data on line {$this->currentLine} for file {$this->filePath}";
            throw new \UnexpectedValueException($message);
        }

        $count = count($values);
        if ($count !== $this->headerCount) {
            $message = "Number of columns not matching on line {$this->currentLine} for file {$this->filePath}: ";
            $message .= "{$count} columns for {$this->headerCount} headers";
            throw new \UnexpectedValueException($message);
        }

        return array_combine($this->headers, $values);
    }

    /**
     * This methods rewinds the file to the first line of data, skipping the headers
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function rewind()
    {
        $this->assertOpened();
        if (!rewind($this->handler)) {
            throw new \RuntimeException('Unable to rewind CSV resource file');
        }
        $this->currentLine = 0;
        if (!$this->manualHeaders) {
            $this->readRaw(); // skip headers
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @return int
     */
    public function tell()
    {
        $this->assertOpened();

        return ftell($this->handler);
    }

    /**
     * @param int $offset
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    public function seek($offset)
    {
        $this->assertOpened();

        return fseek($this->handler, $offset);
    }

    /**
     * @return bool
     */
    public function close()
    {
        if ($this->isClosed) {
            return true;
        }

        $this->isClosed = fclose($this->handler);

        return $this->isClosed;
    }

    /**
     * Closes the resource when the object is destroyed
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @throws \RuntimeException
     */
    protected function assertOpened()
    {
        if ($this->isClosed) {
            throw new \RuntimeException('Resource handler was closed earlier');
        }
    }
}
