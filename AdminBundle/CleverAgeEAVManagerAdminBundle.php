<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle;

use CleverAge\EAVManager\AdminBundle\DependencyInjection\CleverAgeEAVManagerAdminExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class CleverAgeEAVManagerAdminBundle
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
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
