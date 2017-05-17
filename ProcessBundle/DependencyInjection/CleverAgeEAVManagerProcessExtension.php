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
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CleverAgeEAVManagerProcessExtension extends Extension
{
    /** @var array */
    protected $globalConfig;

    /**
     * {@inheritdoc}
     *
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
        foreach ((array) $config['transformer_list'] as $code => $transformerConfiguration) {
            $this->addTransformerDefinition($code, $transformerConfiguration, $container);
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
            new Parameter('eavmanager_process.process_configuration.class'),
            [$code, $processConfiguration]
        );
        $definition->addTag('eavmanager.process_config');
        $container->setDefinition('eavmanager.process_config.'.$code, $definition);
    }

    /**
     * @param string           $code
     * @param array            $transformerConf
     * @param ContainerBuilder $container
     *
     * @throws BadMethodCallException
     */
    protected function addTransformerDefinition($code, $transformerConf, ContainerBuilder $container)
    {
        // Resolve given references for array or simple values
        $transformerConf['service'] = $this->resolveServiceReference($transformerConf['service']);
        foreach ($transformerConf['mapping'] as $attribute => $attributeConfig) {
            if (isset($transformerConf['mapping'][$attribute]['transformer'])) {
                $attrTransformer = $transformerConf['mapping'][$attribute]['transformer'];
                if (is_array($transformerConf['mapping'][$attribute]['transformer'])) {
                    $transformerConf['mapping'][$attribute]['transformer'] = array_map(
                        [$this, 'resolveServiceReference'],
                        $attrTransformer
                    );
                } else {
                    $transformerConf['mapping'][$attribute]['transformer'] = $this->resolveServiceReference(
                        $attrTransformer
                    );
                }
            }
        }

        $definition = new Definition(
            new Parameter('eavmanager_process.transformer_configuration.class'),
            [$code, $transformerConf]
        );
        $definition->addTag('eavmanager.transformer_config');
        $container->setDefinition('eavmanager.transformer_config.'.$code, $definition);
    }

    /**
     * @param string $serviceId
     *
     * @return Reference
     */
    public function resolveServiceReference($serviceId)
    {
        if (null === $serviceId) {
            return null;
        }

        return new Reference(ltrim($serviceId, '@'));
    }
}
