<?php

namespace CleverAge\EAVManager\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * General configuration for EAVManager
 */
class Configuration implements ConfigurationInterface
{
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
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->rootAlias);

        $rootNode
            ->children()
                ->arrayNode('wysiwyg')
                    ->defaultValue([])
                    ->useAttributeAsKey('code')
                    ->prototype('variable')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
