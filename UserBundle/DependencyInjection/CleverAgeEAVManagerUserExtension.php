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

use Sidus\BaseBundle\DependencyInjection\SidusBaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class CleverAgeEAVManagerUserExtension extends SidusBaseExtension
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

        parent::load($configs, $container);
    }

    /**
     * @return Configuration
     */
    protected function createConfiguration()
    {
        return new Configuration();
    }
}
