<?php

namespace CleverAge\EAVManager\Component\Controller;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method string generateUrl($route, $parameters = [], $referenceType)
 * @method Registry getDoctrine
 * @method User getUser
 * @method addFlash($key, $message)
 * @method redirect($url, $status)
 * @property ContainerInterface $container
 */
trait BaseControllerTrait
{
    /**
     * @param Request $request
     * @param array   $parameters
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getCurrentUri(Request $request, array $parameters = [])
    {
        $params = $request->attributes->get('_route_params');
        if ($request->get('target')) {
            $params['target'] = $request->get('target');
        }

        return $this->generateUrl($request->attributes->get('_route'), array_merge($params, $parameters));
    }

    /**
     * Alias to return the entity manager
     *
     * @param string|null $persistentManagerName
     * @return EntityManager
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function getManager($persistentManagerName = null)
    {
        return $this->getDoctrine()->getManager($persistentManagerName);
    }


    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function isElasticaEnabled()
    {
        return $this->container->getParameter('elastica_enabled');
    }

    /**
     * @throws InvalidArgumentException
     * @throws ServiceCircularReferenceException|ServiceNotFoundException
     *
     * @return bool
     */
    protected function isElasticaUp()
    {
        if (!$this->isElasticaEnabled()) {
            return false;
        }
        try {
            $this->container->get('fos_elastica.client')->getStatus();

            return true;
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Elastic search is down, some features will be locked');
        }

        return false;
    }
}
