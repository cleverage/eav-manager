<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Listener\Routing;

use Sidus\AdminBundle\Admin\Admin;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Add no-cache header to all Http Responses from the admin.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class CacheListener
{
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

        $admin = $event->getRequest()->attributes->get('_admin');
        if (!$admin instanceof Admin) {
            throw new \UnexpectedValueException('_admin request attribute is not an Admin object');
        }

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
