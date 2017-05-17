<?php

namespace CleverAge\EAVManager\LayoutBundle\Controller;

use CleverAge\EAVManager\Component\Controller\BaseControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Override this controller to create a custom dashboard.
 */
class DashboardController extends Controller
{
    use BaseControllerTrait;

    /**
     * @Template()
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
