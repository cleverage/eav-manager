<?php

namespace CleverAge\EAVManager\ProcessBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
    public function __construct($root = 'clever_age_eav_manager_process')
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
        $definition = $rootNode
            ->children();

        // Process list
        $processListDefinition = $definition->arrayNode('process_list')->useAttributeAsKey('code')
            ->prototype('array')->performNoDeepMerging()->cannotBeOverwritten()
            ->children();

        $this->appendProcessConfigDefinition($processListDefinition);

        $processListDefinition->end()
            ->end()
            ->end();

        // Transformer list
        $transformerListDefinition = $definition->arrayNode('transformer_list')->useAttributeAsKey('code')
            ->prototype('array')->performNoDeepMerging()->cannotBeOverwritten()
            ->children();

        $this->appendTransformerConfigDefinition($transformerListDefinition);

        $transformerListDefinition->end()
            ->end()
            ->end();

        $definition->end();

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $definition
     */
    protected function appendProcessConfigDefinition(NodeBuilder $definition)
    {
        $definition->scalarNode('service')->defaultValue('@eavmanager.process_manager')->end()
            // TODO use more accurate modelisation with arrayNode ?
            ->variableNode('subprocess')->end();
    }

    /**
     * @param NodeBuilder $definition
     */
    protected function appendTransformerConfigDefinition(NodeBuilder $definition)
    {
        $transformerConfigDefintion = $definition
            ->scalarNode('service')->defaultValue('@eavmanager.transformer_manager')->end()
            ->arrayNode('mapping')->isRequired()
            ->prototype('array')->performNoDeepMerging()->cannotBeOverwritten()
            ->children();

        $transformerConfigDefintion
            ->variableNode('code')->end()
            ->scalarNode('constant')->end()
            ->variableNode('transformer')->end();

        $definition
            ->end()
            ->end()
            ->end();
    }
}
