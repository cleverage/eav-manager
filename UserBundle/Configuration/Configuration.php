<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
