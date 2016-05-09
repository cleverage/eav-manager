<?php

namespace CleverAge\EAVManager\ImportBundle;

use Sidus\EAVModelBundle\DependencyInjection\Compiler\GenericCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CleverAgeEAVManagerImportBundle extends Bundle
{
    /**
     * Adding compiler passes to inject services into configuration handlers
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GenericCompilerPass(
            'eavmanager.import_configuration.handler',
            'eavmanager.import',
            'addImport'
        ));
    }
}
