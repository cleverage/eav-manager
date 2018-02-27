<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\SecurityBundle\Security\Core\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;
use Doctrine\Common\Collections\Collection;

/**
 * Used to work with roles.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class RoleHierarchy extends BaseRoleHierarchy
{
    /** @var LeafRole[]|Collection */
    protected $treeHierarchy;

    /**
     * @param array $hierarchy An array defining the hierarchy
     */
    public function __construct(array $hierarchy)
    {
        parent::__construct($hierarchy);

        /** @var LeafRole[] $flatRoles */
        $flatRoles = [];
        // Build proper tree hierarchy from security config
        foreach ($hierarchy as $rootRole => $roles) {
            if (!isset($flatRoles[$rootRole])) {
                $flatRoles[$rootRole] = new LeafRole($rootRole);
            }
            /** @var array $roles */
            foreach ($roles as $leafRole) {
                if (!isset($flatRoles[$leafRole])) {
                    $flatRoles[$leafRole] = new LeafRole($leafRole);
                }
                $flatRoles[$rootRole]->addChild($flatRoles[$leafRole]);
            }
        }
        $this->treeHierarchy = new ArrayCollection();
        foreach ($flatRoles as $role) {
            if (!$role->getParent()) {
                $this->treeHierarchy[] = $role;
            }
        }
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @return LeafRole[]|Collection
     */
    public function getTreeHierarchy()
    {
        return $this->treeHierarchy;
    }
}
