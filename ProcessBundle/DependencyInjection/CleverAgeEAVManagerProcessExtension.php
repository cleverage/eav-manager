<?php

namespace CleverAge\EAVManager\ProcessBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CleverAgeEAVManagerProcessExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $loader->load('manager.yml');
        $loader->load('registry.yml');
        $loader->load('task.yml');

        $processConfigurationRegistry = $container->getDefinition('eavmanager_process.registry.process_configuration');
        $processConfigurationRegistry->replaceArgument(0, $config['configurations']);
    }
}
