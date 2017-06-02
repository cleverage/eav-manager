<?php

namespace CleverAge\EAVManager\ProcessBundle\Command;

use CleverAge\EAVManager\ProcessBundle\Manager\ProcessManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run a process from the command line interface
 */
class ExecuteProcessCommand extends ContainerAwareCommand
{
    /** @var ProcessManager */
    protected $processManager;

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('eavmanager:process:execute');
        $this->addArgument('processCode', InputArgument::REQUIRED, 'The code of the process to execute');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->processManager = $this->getContainer()->get('eavmanager_process.manager.process');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->processManager->execute($input->getArgument('processCode'), $output);
    }
}
