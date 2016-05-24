<?php

namespace CleverAge\EAVManager\AdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CleverAgeEAVManagerAdminExtension extends Extension
{
    /** @var array */
    protected $globalConfig;

    /** @var string */
    protected $rootAlias;

    /**
     * @param string $rootAlias
     */
    public function __construct($rootAlias)
    {
        $this->rootAlias = $rootAlias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->rootAlias;
    }

    /**
     * {@inheritdoc}
     * @throws BadMethodCallException
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->createConfiguration();
        $this->globalConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter($this->rootAlias.'.configuration', $this->globalConfig);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @return Configuration
     * @throws BadMethodCallException
     */
    protected function createConfiguration()
    {
        return new Configuration($this->getAlias());
    }
}
