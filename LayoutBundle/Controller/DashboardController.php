<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
