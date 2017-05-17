<?php

namespace CleverAge\EAVManager\ImportBundle\Entity;

use CleverAge\EAVManager\ImportBundle\Exception\InvalidImportException;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represent one import error, with a given reason.
 *
 * @ORM\Entity
 * @ORM\Table(name="eavmanager_import_error_log")
 */
class ImportErrorLog
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\ImportBundle\Entity\ImportHistory", inversedBy="errorLogs")
     * @ORM\JoinColumn(name="history_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     *
     * @var ImportHistory
     */
    protected $history;

    /**
     * @ORM\Column
     *
     * @var string
     */
    protected $message;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    protected $errorJson;

    /**
     * @param InvalidImportException $exception
     *
     * @return ImportErrorLog
     */
    public static function createFromError(InvalidImportException $exception): ImportErrorLog
    {
        $errorLog = new self();
        $errorLog->setMessage($exception->getMessage());
        $errorLog->setErrorJson($exception->jsonSerialize());

        return $errorLog;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param mixed $history
     */
    public function setHistory($history)
    {
        $this->history = $history;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getErrorJson(): string
    {
        return $this->errorJson;
    }

    /**
     * @param string $errorJson
     */
    public function setErrorJson(string $errorJson)
    {
        $this->errorJson = $errorJson;
    }
}
