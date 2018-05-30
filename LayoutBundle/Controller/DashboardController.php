<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\LayoutBundle\Controller;

use CleverAge\EAVManager\Component\Controller\BaseControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Override this controller to create a custom dashboard.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DashboardController extends Controller
{
    use BaseControllerTrait;

    /**
     * @Template("@CleverAgeEAVManagerLayout/Dashboard/dashboard.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function dashboardAction(Request $request)
    {
        return [];
    }
}
