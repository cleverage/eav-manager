<?php

namespace CleverAge\EAVManager\ProcessBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CleverAgeEAVManagerProcessExtension extends Extension
{
    /** @var array */
    protected $globalConfig;

    /**
     * {@inheritdoc}
     * @throws BadMethodCallException
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this->globalConfig = $config;

        // Automatically declare a service for each import configured
        foreach ((array) $config['process_list'] as $code => $processConfiguration) {
            $this->addProcessDefinition($code, $processConfiguration, $container);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param string           $code
     * @param array            $processConfiguration
     * @param ContainerBuilder $container
     *
     * @throws BadMethodCallException
     */
    protected function addProcessDefinition($code, $processConfiguration, ContainerBuilder $container)
    {
        // Resolve given references
        $processConfiguration['service'] = $this->resolveServiceReference($processConfiguration['service']);
        foreach ($processConfiguration['subprocess'] as $i => $subprocess) {
            $processConfiguration['subprocess'][$i] = $this->resolveServiceReference($subprocess);
        }

        $definition = new Definition(
            new Parameter('eavmanager_process.process_configuration.class'), [
                $code,
                $processConfiguration,
            ]
        );
        $definition->addTag('eavmanager.process_config');
        $container->setDefinition('eavmanager.process_config.'.$code, $definition);
    }

    /**
     * @param string $serviceId
     *
     * @return Reference
     */
    protected function resolveServiceReference($serviceId)
    {
        if (null === $serviceId) {
            return null;
        }

        return new Reference(ltrim($serviceId, '@'));
    }
}
