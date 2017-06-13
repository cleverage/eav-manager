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

namespace CleverAge\EAVManager\UserBundle\Configuration;

/**
 * Handles the configuration of the user management system.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class Configuration
{
    /** @var string */
    protected $mailerCompany;

    /** @var array */
    protected $mailerFrom;

    /** @var string */
    protected $homeRoute;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->mailerCompany = $config['mailer']['company'];
        $this->mailerFrom = [$config['mailer']['from_email'] => $config['mailer']['from_name']];
        $this->homeRoute = $config['home_route'];
    }

    /**
     * @return string
     */
    public function getMailerCompany()
    {
        return $this->mailerCompany;
    }

    /**
     * @return array
     */
    public function getMailerFrom()
    {
        return $this->mailerFrom;
    }

    /**
     * @return string
     */
    public function getHomeRoute()
    {
        return $this->homeRoute;
    }
}
