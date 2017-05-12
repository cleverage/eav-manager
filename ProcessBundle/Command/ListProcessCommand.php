<?php

namespace CleverAge\EAVManager\ProcessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListProcessCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('eavmanager:process:list');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processConfigurations = $this->getContainer()->get(
            'eavmanager.process_config.registry'
        )->getProcessConfigurations();
        $processConfigurationCount = count($processConfigurations);
        $output->writeln("<info>There is {$processConfigurationCount} process configurations defined :</info>");
        foreach ($processConfigurations as $processConfiguration) {
            $countSubprocess = count($processConfiguration->getSubprocess());
            $output->writeln(
                "<info> - </info>{$processConfiguration->getCode()}<info> with {$countSubprocess} subprocesses</info>"
            );
        }
    }
}
