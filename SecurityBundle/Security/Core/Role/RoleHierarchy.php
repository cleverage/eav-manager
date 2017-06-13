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
