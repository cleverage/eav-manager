<?php

namespace CleverAge\EAVManager\ProcessBundle\Manager;

use CleverAge\EAVManager\ProcessBundle\Configuration\ProcessConfiguration;
use CleverAge\EAVManager\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\EAVManager\ProcessBundle\Exception\MissingProcessException;
use CleverAge\EAVManager\ProcessBundle\Model\FinalizableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\InitializableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\TaskInterface;
use Doctrine\ORM\EntityManager;
use ProcessBundle\Entity\ProcessHistory;
use ProcessBundle\Model\ProcessState;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProcessManager
{
    /** @var ContainerInterface */
    protected $container;

    /** @var LoggerInterface */
    protected $logger;

    /** @var EntityManager */
    protected $entityManager;

    /** @var ProcessConfiguration[] */
    protected $processConfigurations;

    /**
     * @param string $processCode
     *
     * @throws \Exception
     */
    public function execute(string $processCode)
    {
        $processConfiguration = $this->getProcessConfiguration($processCode);
        $state = $this->initializeState($processConfiguration);

        // Fetch first task to execute
        $taskConfiguration = $processConfiguration->getEntryPoint();

        // First initialize the whole stack
        $this->initialize($taskConfiguration, $state);

        // Then launch the process
        $this->process($taskConfiguration, $state);

        // Finalize the process
        $this->finalize($taskConfiguration, $state);
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     *
     * @throws \Exception
     */
    public function initialize(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $this->doInitializeTask($taskConfiguration, $state);
        $this->handleState($state);

        foreach ($state->getProcessConfiguration()->getNextTasks($taskConfiguration) as $nextTask) {
            $this->initialize($nextTask, $state);
        }
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     *
     * @throws \Exception
     */
    public function process(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $task = $this->doProcessTask($taskConfiguration, $state);

        if ($task instanceof IterableTaskInterface) {
            // @todo
        }
        $this->handleState($state);

        foreach ($state->getProcessConfiguration()->getNextTasks($taskConfiguration) as $nextTask) {
            $this->process($nextTask, $state);
        }
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     *
     * @throws \Exception
     */
    public function finalize(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $this->doFinalizeTask($taskConfiguration, $state);
        $this->handleState($state);

        foreach ($state->getProcessConfiguration()->getNextTasks($taskConfiguration) as $nextTask) {
            $this->finalize($nextTask, $state);
        }
    }

    /**
     * @param string $processCode
     *
     * @throws \CleverAge\EAVManager\ProcessBundle\Exception\MissingProcessException
     *
     * @return ProcessConfiguration
     */
    public function getProcessConfiguration(string $processCode): ProcessConfiguration
    {
        if (!array_key_exists($processCode, $this->processConfigurations)) {
            throw new MissingProcessException($processCode);
        }

        return $this->processConfigurations[$processCode];
    }

    /**
     * @param ProcessConfiguration $processConfiguration
     *
     * @throws \InvalidArgumentException
     *
     * @return ProcessState
     */
    protected function initializeState(ProcessConfiguration $processConfiguration)
    {
        $processHistory = new ProcessHistory($processConfiguration);
        $this->entityManager->persist($processHistory);

        return new ProcessState($processConfiguration, $processHistory);
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     */
    protected function doInitializeTask(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $state->setTaskConfiguration($taskConfiguration);
        try {
            $task = $this->container->get($taskConfiguration->getService());
            if (!$task instanceof TaskInterface) {
                throw new \UnexpectedValueException("Task '{$taskConfiguration->getCode()}' is not a TaskInterface");
            }

            if ($task instanceof InitializableTaskInterface) {
                $task->initialize($state);
            }
        } catch (\Exception $e) {
            $state->log($e->getMessage());
            $state->setStopped(true);
            $state->setException($e);
        }
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState $state
     *
     * @return TaskInterface
     */
    protected function doProcessTask(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $state->setInput($state->getOutput()); // Switch output to input
        $state->setOutput(null);
        $state->setTaskConfiguration($taskConfiguration);

        /** @var TaskInterface $task */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $task = $this->container->get($taskConfiguration->getService());
        try {
            $task->execute($state);
        } catch (\Exception $e) {
            $state->log($e->getMessage());
            $state->setStopped(true);
            $state->setException($e);
        }

        return $task;
    }

    /**
     * @param $taskConfiguration
     * @param $state
     */
    protected function doFinalizeTask(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $state->setInput($state->getOutput()); // Switch output to input @todo Does it make sense ?
        $state->setOutput(null);
        $state->setTaskConfiguration($taskConfiguration);
        try {
            $task = $this->container->get($taskConfiguration->getService());
            if ($task instanceof FinalizableTaskInterface) {
                $task->finalize($state);
            }
        } catch (\Exception $e) {
            $state->log($e->getMessage());
            $state->setStopped(true);
            $state->setException($e);
        }
    }

    /**
     * @param ProcessState $state
     *
     * @throws \Exception
     */
    protected function handleState(ProcessState $state)
    {
        // save state
        foreach ($state->getTaskHistories() as $taskHistory) {
            if ($consoleOutput = $state->getConsoleOutput()) {
                $consoleOutput->writeln("<error>{$taskHistory->getMessage()}</error>"); // @todo better logging
            }
            $this->entityManager->persist($taskHistory);
            $this->entityManager->flush($taskHistory);
        }

        if ($state->getException() || $state->isStopped()) {
            $processHistory = $state->getProcessHistory();
            $processHistory->setEndDate(new \DateTime());
            $processHistory->setState(ProcessHistory::STATE_FAILED);
            $this->entityManager->flush($processHistory);

            throw new \RuntimeException(
                "Process {$state->getProcessConfiguration()->getCode()} as failed",
                -1,
                $state->getException()
            );
        }
    }
}
