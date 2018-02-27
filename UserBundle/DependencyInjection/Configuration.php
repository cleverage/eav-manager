<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class Configuration implements ConfigurationInterface
{
    /** @var string */
    protected $root;

    /**
     * @param string $root
     */
    public function __construct($root = 'clever_age_eav_manager_user')
    {
        $this->root = $root;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->root);

        $rootNode
            ->children()
            ->scalarNode('home_route')->defaultValue('eavmanager_layout.dashboard')->end()
            ->append($this->getMailerConfigurationTreeBuilder())
            ->end();

        return $treeBuilder;
    }

    /**
     * @return NodeDefinition
     *
     * @throws \RuntimeException
     */
    protected function getMailerConfigurationTreeBuilder()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('mailer');
        $node
            ->children()
            ->scalarNode('company')->end()
            ->scalarNode('from_email')->end()
            ->scalarNode('from_name')->end()
            ->end();

        return $node;
    }
}
