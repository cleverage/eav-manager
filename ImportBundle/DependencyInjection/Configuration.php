<?php

namespace CleverAge\EAVManager\ImportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /** @var string */
    protected $root;

    /**
     * @param string $root
     */
    public function __construct($root = 'clever_age_eav_manager_import')
    {
        $this->root = $root;
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->root);
        $importConfigDefinition = $rootNode
            ->children()
                ->arrayNode('configurations')
                    ->useAttributeAsKey('code')
                    ->prototype('array')
                        ->performNoDeepMerging()
                        ->cannotBeOverwritten()
                        ->children();

        $this->appendImportConfigDefinition($importConfigDefinition);

        $importConfigDefinition
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $importConfigDefinition
     */
    protected function appendImportConfigDefinition(NodeBuilder $importConfigDefinition)
    {
        $importConfigDefinition
            ->scalarNode('file_path')->isRequired()->end()
            ->scalarNode('family')->isRequired()->end()
            ->scalarNode('service')->defaultValue('@eavmanager_import.eav_data_importer')->end()
            ->scalarNode('transformer')->end()
            ->variableNode('mapping')->end()
            ->variableNode('options')->end();
    }
}
