<?php

namespace CleverAge\EAVManager\AdminBundle;

use CleverAge\EAVManager\AdminBundle\DependencyInjection\CleverAgeEAVManagerAdminExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CleverAgeEAVManagerAdminBundle extends Bundle
{
    /**
     * Changing default root alias.
     *
     * @return mixed
     */
    public function getContainerExtension()
    {
        return new CleverAgeEAVManagerAdminExtension('cleverage_eavmanager');
    }
}
