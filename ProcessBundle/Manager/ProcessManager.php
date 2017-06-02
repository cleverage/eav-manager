<?php

namespace CleverAge\EAVManager\ProcessBundle\Manager;

use CleverAge\EAVManager\ProcessBundle\Configuration\ProcessConfiguration;
use CleverAge\EAVManager\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\EAVManager\ProcessBundle\Model\FinalizableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\InitializableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\TaskInterface;
use CleverAge\EAVManager\ProcessBundle\Registry\ProcessConfigurationRegistry;
use Doctrine\ORM\EntityManager;
use CleverAge\EAVManager\ProcessBundle\Entity\ProcessHistory;
use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Execute processes
 */
class ProcessManager
{
    /** @var ContainerInterface */
    protected $container;

    /** @var LoggerInterface */
    protected $logger;

    /** @var EntityManager */
    protected $entityManager;

    /** @var ProcessConfigurationRegistry */
    protected $processConfigurationRegistry;

    /**
     * @param ContainerInterface           $container
     * @param LoggerInterface              $logger
     * @param EntityManager                $entityManager
     * @param ProcessConfigurationRegistry $processConfigurationRegistry
     */
    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
        EntityManager $entityManager,
        ProcessConfigurationRegistry $processConfigurationRegistry
    ) {
        $this->container = $container;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->processConfigurationRegistry = $processConfigurationRegistry;
    }

    /**
     * @param string          $processCode
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int
     */
    public function execute(string $processCode, OutputInterface $output = null)
    {
        $processConfiguration = $this->processConfigurationRegistry->getProcessConfiguration($processCode);
        $state = $this->initializeState($processConfiguration, $output);

        // First initialize the whole stack in a linear way, tasks are initialized in the order they are configured
        foreach ($processConfiguration->getTaskConfigurations() as $taskConfiguration) {
            $this->initialize($taskConfiguration, $state);
        }

        // Fetch first task to execute
        $taskConfiguration = $processConfiguration->getEntryPoint();

        // Then launch the process : iterate the tasks tree properly
        $this->process($taskConfiguration, $state);

        // Finalize the process in a linear way
        foreach ($processConfiguration->getTaskConfigurations() as $taskConfiguration) {
            $this->finalize($taskConfiguration, $state);
        }

        $this->endProcess($state);

        return $state->getReturnCode();
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     *
     * @throws \Exception
     */
    protected function initialize(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $this->doInitializeTask($taskConfiguration, $state);
        $this->handleState($state);
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     *
     * @throws \Exception
     */
    protected function process(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $output = $state->getOutput();
        do {
            $state->setInput($output); // Switch output to input
            $state->setOutput(null);
            $this->doProcessTask($taskConfiguration, $state);
            $this->handleState($state);

            foreach ($state->getProcessConfiguration()->getNextTasks($taskConfiguration) as $nextTask) {
                $this->process($nextTask, $state);
            }

            $task = $taskConfiguration->getTask();
            $hasMoreItem = $task instanceof IterableTaskInterface ? $task->next($state) : false;
        } while ($hasMoreItem);
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     *
     * @throws \Exception
     */
    protected function finalize(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $this->doFinalizeTask($taskConfiguration, $state);
        $this->handleState($state);

        foreach ($state->getProcessConfiguration()->getNextTasks($taskConfiguration) as $nextTask) {
            $this->finalize($nextTask, $state);
        }
    }

    /**
     * @param ProcessConfiguration $processConfiguration
     * @param OutputInterface      $output
     *
     * @throws \InvalidArgumentException
     *
     * @return ProcessState
     */
    protected function initializeState(ProcessConfiguration $processConfiguration, OutputInterface $output = null)
    {
        $processHistory = new ProcessHistory($processConfiguration);
        $this->entityManager->persist($processHistory);
        $state = new ProcessState($processConfiguration, $processHistory);
        $state->setConsoleOutput($output);

        return $state;
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \UnexpectedValueException
     */
    protected function doInitializeTask(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $state->setTaskConfiguration($taskConfiguration);
        $task = $this->container->get(ltrim($taskConfiguration->getServiceReference(), '@'));
        if (!$task instanceof TaskInterface) {
            throw new \UnexpectedValueException("Task '{$taskConfiguration->getCode()}' is not a TaskInterface");
        }
        $taskConfiguration->setTask($task);

        if ($task instanceof InitializableTaskInterface) {
            try {
                $task->initialize($state);
            } catch (\Exception $e) {
                $state->log($e->getMessage(), LogLevel::CRITICAL);
                $state->stop($e);
            }
        }
    }

    /**
     * @param TaskConfiguration $taskConfiguration
     * @param ProcessState      $state
     */
    protected function doProcessTask(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $state->setTaskConfiguration($taskConfiguration);
        try {
            $taskConfiguration->getTask()->execute($state);
        } catch (\Exception $e) {
            $state->log($e->getMessage(), LogLevel::CRITICAL);
            $state->stop($e);
        }
    }

    /**
     * @param $taskConfiguration
     * @param $state
     */
    protected function doFinalizeTask(TaskConfiguration $taskConfiguration, ProcessState $state)
    {
        $state->setTaskConfiguration($taskConfiguration);
        $task = $taskConfiguration->getTask();
        if ($task instanceof FinalizableTaskInterface) {
            try {
                $task->finalize($state);
            } catch (\Exception $e) {
                $state->log($e->getMessage(), LogLevel::CRITICAL);
                $state->stop($e);
            }
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
        $state->clearTaskHistories();

        if ($state->getException() || $state->isStopped()) {
            $processHistory = $state->getProcessHistory();
            $processHistory->setFailed();
            $this->entityManager->flush($processHistory);

            throw new \RuntimeException(
                "Process {$state->getProcessConfiguration()->getCode()} as failed",
                -1,
                $state->getException()
            );
        }
    }

    /**
     * @param ProcessState $state
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function endProcess(ProcessState $state)
    {
        $processHistory = $state->getProcessHistory();
        $processHistory->setSuccess();
        $this->entityManager->flush($processHistory);
    }
}
