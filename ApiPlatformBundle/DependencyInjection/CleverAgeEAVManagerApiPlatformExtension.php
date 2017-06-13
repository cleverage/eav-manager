<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CleverAgeEAVManagerApiPlatformExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load normalizers only if ApiPlatformBundle is enabled
        if (array_key_exists('ApiPlatformBundle', $container->getParameter('kernel.bundles'))) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
            $loader->load('filter.yml');
            $loader->load('metadata.yml');
            $loader->load('provider.yml');

            // Normalizers
            $loader->load('normalizer/attribute.yml');
            $loader->load('normalizer/data.yml');
            $loader->load('normalizer/family.yml');
            $loader->load('normalizer/user.yml');
        }
    }
}
