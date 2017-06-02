<?php

namespace CleverAge\EAVManager\ProcessBundle\Entity;

use CleverAge\EAVManager\ProcessBundle\Configuration\TaskConfiguration;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LogLevel;

/**
 * History element for a task
 *
 * @ORM\Table(name="clever_task_history", indexes={
 *     @ORM\Index(name="task_code", columns={"task_code"}),
 *     @ORM\Index(name="logged_at", columns={"logged_at"}),
 *     @ORM\Index(name="level", columns={"level"})
 * })
 * @ORM\Entity(repositoryClass="CleverAge\EAVManager\ProcessBundle\Entity\TaskHistoryRepository")
 */
class TaskHistory
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ProcessHistory
     *
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\ProcessBundle\Entity\ProcessHistory", inversedBy="taskHistories")
     * @ORM\JoinColumn(name="process_history_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $processHistory;

    /**
     * @var string
     *
     * @ORM\Column(name="task_code", type="string")
     */
    protected $taskCode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="string", length=16)
     */
    protected $level = LogLevel::ERROR;

    /**
     * @var array
     *
     * @ORM\Column(name="context", type="json_array", nullable=true)
     */
    protected $context;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", nullable=true)
     */
    protected $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

    /**
     * TaskHistory constructor.
     *
     * @param ProcessHistory    $processHistory
     * @param TaskConfiguration $taskConfiguration
     */
    public function __construct(ProcessHistory $processHistory, TaskConfiguration $taskConfiguration)
    {
        $this->processHistory = $processHistory;
        $this->taskCode = $taskConfiguration->getCode();
        $this->loggedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ProcessHistory
     */
    public function getProcessHistory(): ProcessHistory
    {
        return $this->processHistory;
    }

    /**
     * @return string
     */
    public function getTaskCode(): string
    {
        return $this->taskCode;
    }

    /**
     * @return \DateTime
     */
    public function getLoggedAt(): \DateTime
    {
        return $this->loggedAt;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level)
    {
        $this->level = $level;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context = null)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference(string $reference = null)
    {
        $this->reference = $reference;
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
    public function setMessage(string $message = null)
    {
        $this->message = $message;
    }
}
