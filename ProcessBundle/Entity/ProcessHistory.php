<?php

namespace CleverAge\EAVManager\ProcessBundle\Entity;

use CleverAge\EAVManager\ProcessBundle\Configuration\ProcessConfiguration;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="clever_process_history", indexes={
 *     @ORM\Index(name="process_code", columns={"process_code"}),
 *     @ORM\Index(name="start_date", columns={"start_date"}),
 *     @ORM\Index(name="end_date", columns={"end_date"}),
 *     @ORM\Index(name="state", columns={"state"})
 * })
 * @ORM\Entity(repositoryClass="CleverAge\EAVManager\ProcessBundle\Entity\ProcessHistoryRepository")
 */
class ProcessHistory
{
    const STATE_STARTED = 'started';
    const STATE_SUCCESS = 'success';
    const STATE_FAILED = 'failed';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="process_code", type="string")
     */
    protected $processCode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    protected $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=16)
     */
    protected $state = self::STATE_STARTED;

    /**
     * @var TaskHistory[]
     *
     * @ORM\OneToMany(targetEntity="CleverAge\EAVManager\ProcessBundle\Entity\TaskHistory", mappedBy="processHistory",
     *                                                    cascade={"persist", "remove", "detach"}, orphanRemoval=true)
     */
    protected $taskHistories;

    /**
     * @param ProcessConfiguration $processConfiguration
     */
    public function __construct(ProcessConfiguration $processConfiguration)
    {
        $this->processCode = $processConfiguration->getCode();
        $this->startDate = new \DateTime();
        $this->taskHistories = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getProcessCode(): string
    {
        return $this->processCode;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return TaskHistory[]
     */
    public function getTaskHistories()
    {
        return $this->taskHistories;
    }

    /**
     * @param TaskHistory $taskHistory
     */
    public function addTaskHistory(TaskHistory $taskHistory)
    {
        $this->taskHistories[] = $taskHistory;
    }

    /**
     * Set the process as failed
     */
    public function setFailed()
    {
        $this->endDate = new \DateTime();
        $this->state = self::STATE_FAILED;
    }

    /**
     * Set the process as succeded
     */
    public function setSuccess()
    {
        $this->endDate = new \DateTime();
        $this->state = self::STATE_SUCCESS;
    }
}
