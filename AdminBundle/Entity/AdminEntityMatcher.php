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

namespace CleverAge\EAVManager\AdminBundle\Entity;

use Sidus\AdminBundle\Admin\Admin;
use Sidus\AdminBundle\Entity\AdminEntityMatcher as BaseAdminEntityMatcher;
use Sidus\EAVModelBundle\Entity\DataInterface;

/**
 * Overrides the base entity matcher to use options inside the admin configurations.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class AdminEntityMatcher extends BaseAdminEntityMatcher
{
    /**
     * @param mixed $entity
     *
     * @return Admin
     *
     * @throws \UnexpectedValueException
     */
    public function getAdminForEntity($entity)
    {
        $default = null;
        foreach ($this->adminConfigurationHandler->getAdmins() as $admin) {
            if (is_a($entity, $admin->getEntity())) {
                if ($entity instanceof DataInterface) {
                    $default = $default ?: $admin;
                    foreach ($admin->getOption('families', []) as $family => $config) {
                        if ($entity->getFamilyCode() === $family) {
                            return $admin;
                        }
                    }
                } else {
                    return $admin;
                }
            }
        }

        if ($default) {
            // Or throw exception anyway ?
            return $default;
        }
        $class = get_class($entity);
        throw new \UnexpectedValueException("No admin matching for entity {$class}");
    }
}
