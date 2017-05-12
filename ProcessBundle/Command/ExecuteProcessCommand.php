<?php

namespace CleverAge\EAVManager\ProcessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @TODO describe class usage
 */
class ExecuteProcessCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('eavmanager:process:execute');
        $this->addArgument('process_code');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('process_code');
        $config = $this->getContainer()->get('eavmanager.process_config.registry')->getProcessConfiguration($code);

        $config->getProcessManagerService()->execute($config->getSubprocess());
    }
}
