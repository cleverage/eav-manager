<?php

namespace CleverAge\EAVManager\ProcessBundle\Command;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessConfigurationRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListProcessCommand extends ContainerAwareCommand
{
    /** @var ProcessConfigurationRegistry */
    protected $processConfigRegistry;

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('eavmanager:process:list');
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
        $this->processConfigRegistry = $this->getContainer()->get('eavmanager.process_config.registry');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processConfigurations = $this->processConfigRegistry->getProcessConfigurations();
        $processConfigurationCount = count($processConfigurations);
        $output->writeln("<info>There are {$processConfigurationCount} process configurations defined :</info>");
        foreach ($processConfigurations as $processConfiguration) {
            $countSubprocess = count($processConfiguration->getSubprocess());
            $output->writeln(
                "<info> - </info>{$processConfiguration->getCode()}<info> with {$countSubprocess} subprocesses</info>"
            );
        }
    }
}
