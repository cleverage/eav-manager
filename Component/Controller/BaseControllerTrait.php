<?php

namespace CleverAge\EAVManager\Component\Controller;


use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Elastica\Exception\Connection\HttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @return bool
     * @throws ServiceCircularReferenceException|ServiceNotFoundException
     */
    protected function isElasticaUp()
    {
        try {
            $this->container->get('fos_elastica.client')->getStatus();

            return true;
        } catch (HttpException $e) {
            $this->addFlash('warning', 'Elastic search is down, some features will be locked');
        }

        return false;
    }

    /**
     * @param mixed $entity
     * @param string $action
     * @param array $parameters
     * @param int $referenceType
     * @param int $status
     * @return RedirectResponse
     * @throws \Exception
     */
    protected function redirectToEntity(
        $entity,
        $action,
        array $parameters = [],
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
        $status = 302
    ) {
        $url = $this->container->get('sidus_admin.routing.admin_router')
            ->generateEntityPath($entity, $action, $parameters, $referenceType);

        return new RedirectResponse($url, $status);
    }

    /**
     * @param string $admin
     * @param string $action
     * @param array  $parameters
     * @param int    $referenceType
     * @param int    $status
     * @return RedirectResponse
     * @throws \Exception
     */
    protected function redirectToAdmin(
        $admin,
        $action,
        array $parameters = [],
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
        $status = 302
    ) {
        $url = $this->container->get('sidus_admin.routing.admin_router')
            ->generateAdminPath($admin, $action, $parameters, $referenceType);

        return new RedirectResponse($url, $status);
    }
}
