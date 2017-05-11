<?php

namespace CleverAge\EAVManager\ImportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CleverAge\EAVManager\ImportBundle\Entity\ImportErrorLog;

/**
 * Represent one import that is or have been processed
 *
 * @ORM\Entity
 * @ORM\Table(name="eavmanager_import_history")
 */
class ImportHistory
{

    const STATUS_SUCCESS = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_ERROR = 2;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $importCode;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    protected $startedAt;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $status;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    protected $message;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $finishedAt;

    /**
     * @ORM\OneToMany(targetEntity="ImportErrorLog", mappedBy="history", cascade={"persist", "remove"})
     * @var ImportErrorLog[]
     */
    protected $errorLogs;

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
     * @return string
     */
    public function getImportCode(): string
    {
        return $this->importCode;
    }

    /**
     * @param string $importCode
     */
    public function setImportCode(string $importCode)
    {
        $this->importCode = $importCode;
    }

    /**
     * @return \DateTime
     */
    public function getStartedAt(): \DateTime
    {
        return $this->startedAt;
    }

    /**
     * @param mixed $startedAt
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @return \DateTime
     */
    public function getFinishedAt(): \DateTime
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTime $finishedAt
     */
    public function setFinishedAt(\DateTime $finishedAt)
    {
        $this->finishedAt = $finishedAt;
    }

    /**
     * @return ImportErrorLog[]
     */
    public function getErrorLogs()
    {
        return $this->errorLogs;
    }

    /**
     * @param ImportErrorLog[] $errorLogs
     */
    public function setErrorLogs($errorLogs)
    {
        $this->errorLogs = $errorLogs;
    }

    /**
     * @param ImportErrorLog $errorLogs
     */
    public function addErrorLog($errorLogs)
    {
        if (!$this->errorLogs) {
            $this->errorLogs = [];
        }
        $errorLogs->setHistory($this);
        $this->errorLogs[] = $errorLogs;
    }

}
