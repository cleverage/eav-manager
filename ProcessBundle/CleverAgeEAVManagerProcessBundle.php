<?php

namespace CleverAge\EAVManager\ProcessBundle;

use Sidus\EAVModelBundle\DependencyInjection\Compiler\GenericCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @TODO describe class usage
 */
class CleverAgeEAVManagerProcessBundle extends Bundle
{
    /**
     * Adding compiler passes to inject services into configuration handlers.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new GenericCompilerPass(
                'eavmanager.process_config.registry',
                'eavmanager.process_config',
                'addProcessConfiguration'
            )
        );
        $container->addCompilerPass(
            new GenericCompilerPass(
                'eavmanager.transformer_config.registry',
                'eavmanager.transformer_config',
                'addTransformerConfiguration'
            )
        );
    }
}
