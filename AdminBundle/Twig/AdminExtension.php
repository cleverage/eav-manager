<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Twig;

use Sidus\EAVModelBundle\Entity\DataInterface;

/**
 * Adds some minor features to twig
 */
class AdminExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('typeof', [$this, 'getTypeOf']),
        ];
    }

    /**
     * @param object $entity
     * @param bool   $full
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    public function getTypeOf($entity, $full = false)
    {
        if ($entity instanceof DataInterface) {
            if ($full) {
                return (string) $entity->getFamilyCode();
            }

            return (string) $entity->getFamily();
        }

        $refl = new \ReflectionClass($entity);
        if ($full) {
            $refl->getName();
        }

        return $refl->getShortName();
    }
}
