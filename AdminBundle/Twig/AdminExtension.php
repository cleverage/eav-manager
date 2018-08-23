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

use Sidus\DataGridBundle\Renderer\ColumnValueRendererInterface;
use Sidus\EAVModelBundle\Entity\DataInterface;

/**
 * Adds some minor features to twig
 */
class AdminExtension extends \Twig_Extension
{
    /** @var ColumnValueRendererInterface */
    protected $valueRenderer;

    /**
     * @param ColumnValueRendererInterface $valueRenderer
     */
    public function __construct(ColumnValueRendererInterface $valueRenderer)
    {
        $this->valueRenderer = $valueRenderer;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('typeof', [$this, 'getTypeOf']),
            new \Twig_SimpleFilter('instanceof', [$this, 'isInstanceOf']),
        ];
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_value', [$this->valueRenderer, 'renderValue']),
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
    public function getTypeOf($entity, $full = false): string
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

    /**
     * @param mixed  $object
     * @param string $class
     *
     * @return bool
     */
    public function isInstanceOf($object, string $class): bool
    {
        return is_a($object, $class);
    }
}
