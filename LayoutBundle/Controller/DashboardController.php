<?php

namespace CleverAge\EAVManager\LayoutBundle\Controller;

use CleverAge\EAVManager\Component\Controller\BaseControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    use BaseControllerTrait;

    /**
     * @Template()
     * @return array
     * @throws \Exception
     */
    public function dashboardAction(Request $request)
    {
        // This will trigger a flash message if elastica is enabled but down
        $this->isElasticaEnabled() && $this->isElasticaUp();

        return [];
    }
}
