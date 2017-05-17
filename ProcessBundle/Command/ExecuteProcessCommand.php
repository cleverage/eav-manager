<?php

namespace CleverAge\EAVManager\ProcessBundle\Command;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessConfigurationRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @TODO describes class usage
 */
class ExecuteProcessCommand extends ContainerAwareCommand
{
    /** @var ProcessConfigurationRegistry */
    protected $processConfigRegistry;

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('eavmanager:process:execute');
        $this->addArgument('process_code');
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
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('process_code');
        $config = $this->processConfigRegistry->getProcessConfiguration($code);

        $config->getProcessManagerService()->execute($config->getSubprocess());
        // @todo return code !
    }
}
