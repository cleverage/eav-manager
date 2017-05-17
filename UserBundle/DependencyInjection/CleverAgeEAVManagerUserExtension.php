<?php

namespace CleverAge\EAVManager\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CleverAgeEAVManagerUserExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->createConfiguration(), $configs);

        $container->setParameter('eavmanager_user.config', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $loader->load('configuration.yml');
        $loader->load('events.yml');
        $loader->load('forms.yml');
        $loader->load('mailer.yml');
        $loader->load('managers.yml');
        $loader->load('normalizer.yml');
        $loader->load('security.yml');
    }

    /**
     * @return Configuration
     */
    protected function createConfiguration()
    {
        return new Configuration();
    }
}
