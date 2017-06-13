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

namespace CleverAge\EAVManager\AdminBundle\Listener\Routing;

use Psr\Log\LoggerInterface;
use Sidus\AdminBundle\Configuration\AdminConfigurationHandler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Add no-cache header to all Http Reponses from the admin.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class CacheListener
{
    /** @var AdminConfigurationHandler */
    protected $adminConfigurationHandler;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param AdminConfigurationHandler $adminConfigurationHandler
     * @param LoggerInterface           $logger
     */
    public function __construct(AdminConfigurationHandler $adminConfigurationHandler, LoggerInterface $logger)
    {
        $this->adminConfigurationHandler = $adminConfigurationHandler;
        $this->logger = $logger;
    }

    /**
     * @param FilterResponseEvent $event
     *
     * @throws \InvalidArgumentException
     * @throws AccessException
     * @throws \UnexpectedValueException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     * @throws NoSuchOptionException
     * @throws OptionDefinitionException
     * @throws UndefinedOptionsException
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->getRequest()->attributes->has('_admin')) {
            return;
        }

        $adminCode = $event->getRequest()->attributes->get('_admin');

        if (!$this->adminConfigurationHandler->hasAdmin($adminCode)) {
            $this->logger->error("Missing admin with code: '{$adminCode}'");

            return;
        }

        $admin = $this->adminConfigurationHandler->getAdmin($adminCode);

        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                'Pragma' => 'private',
                'Expires' => 0,
            ]
        );

        $headers = $resolver->resolve($admin->getOption('http_cache', []));
        $event->getResponse()->headers->add($headers);
    }
}
