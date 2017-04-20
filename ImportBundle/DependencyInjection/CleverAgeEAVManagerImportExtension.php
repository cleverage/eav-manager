<?php

namespace CleverAge\EAVManager\ImportBundle\DependencyInjection;

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
class CleverAgeEAVManagerImportExtension extends Extension
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
        foreach ((array) $config['configurations'] as $code => $importConfiguration) {
            $this->addImportServiceDefinition($code, $importConfiguration, $container);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param string           $code
     * @param array            $importConfiguration
     * @param ContainerBuilder $container
     *
     * @throws BadMethodCallException
     */
    protected function addImportServiceDefinition($code, $importConfiguration, ContainerBuilder $container)
    {
        // Resolve given references
        foreach (['service', 'transformer', 'source'] as $key) {
            if (isset($importConfiguration[$key])) {
                $importConfiguration[$key] = $this->resolveServiceReference($importConfiguration[$key]);
            }
        }

        $definitionOptions = [
            $code,
            new Reference('sidus_eav_model.family.registry'),
            $importConfiguration,
        ];
        $definition = new Definition(new Parameter('eavmanager_import.import_config.class'), $definitionOptions);
        $definition->addTag('eavmanager.import');
        $container->setDefinition('eavmanager.import.'.$code, $definition);
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
