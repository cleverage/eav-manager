<?php

namespace CleverAge\EAVManager\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CleverAgeEAVManagerUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
