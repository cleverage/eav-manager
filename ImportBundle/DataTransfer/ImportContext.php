<?php

namespace CleverAge\EAVManager\ImportBundle\DataTransfer;


use DateTime;

/**
 * Stores context information between each transaction during import
 */
class ImportContext implements \JsonSerializable
{
    /** @var DateTime */
    protected $startedAt;

    /** @var DateTime */
    protected $endedAt;

    /** @var array */
    protected $processedFiles = [];

    /** @var string */
    protected $lastReference;

    /** @var array */
    protected $references = [];

    /** @var int */
    protected $batchCount;

    /** @var array */
    protected $errors = [];

    /**
     * @param array $data
     * @return ImportContext
     */
    public static function unserialize(array $data)
    {
        $object = new ImportContext();
        foreach ($data as $property => $item) {
            $object->$property = $item;
        }

        return $object;
    }

    /**
     * @param int $batchCount
     */
    public function __construct($batchCount = 30)
    {
        $this->batchCount = $batchCount;
        $this->startedAt = new DateTime();
    }

    /**
     * Called when import terminates
     */
    public function terminate()
    {
        $this->endedAt = new DateTime();
    }

    /**
     * @return DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @return DateTime
     */
    public function getEndedAt()
    {
        return $this->endedAt;
    }

    /**
     * @return string
     */
    public function getLastReference()
    {
        return $this->lastReference;
    }

    /**
     * @return mixed
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @param string $namespace
     * @param string $reference
     * @return bool
     */
    public function hasReference($namespace, $reference)
    {
        if ($namespace === null || null === $reference) {
            return false;
        }
        if (!array_key_exists($namespace, $this->references)) {
            return false;
        }

        return array_key_exists($reference, $this->references[$namespace]);
    }

    /**
     * @param string $namespace
     * @param string $reference
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function getReference($namespace, $reference)
    {
        if (!$this->hasReference($namespace, $reference)) {
            throw new \UnexpectedValueException("Missing reference '{$namespace}/{$reference}'");
        }

        return $this->references[$namespace][$reference];
    }

    /**
     * @param string $namespace
     * @param string $reference
     * @param int    $objectId
     */
    public function addReference($namespace, $reference, $objectId)
    {
        $this->references[$namespace][$reference] = $objectId;
        $this->lastReference = [$namespace, $reference];
    }

    /**
     * @return array
     */
    public function getProcessedFiles()
    {
        return $this->processedFiles;
    }

    /**
     * @param string $processedFile
     * @return bool
     */
    public function hasProcessedFile($processedFile)
    {
        return in_array($processedFile, $this->processedFiles, true);
    }

    /**
     * @param string $processedFile
     */
    public function addProcessedFile($processedFile)
    {
        $this->processedFiles[] = $processedFile;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $message
     */
    public function addError($message)
    {
        $this->errors[time()] = $message;
    }

    /**
     * @return int
     */
    public function getBatchCount()
    {
        return $this->batchCount;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $class = new \ReflectionClass(self::class);
        $result = [];
        foreach ($class->getProperties() as $property) {
            $result[$property->getName()] = $this->{$property->getName()};
        }

        return $result;
    }
}
