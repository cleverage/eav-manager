<?php

namespace CleverAge\EAVManager\UserBundle\Configuration;

/**
 * Handles the configuration of the user management system.
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
