<?php

namespace CleverAge\EAVManager\AdminBundle\Entity;

use Sidus\AdminBundle\Admin\Admin;
use Sidus\AdminBundle\Entity\AdminEntityMatcher as BaseAdminEntityMatcher;
use Sidus\EAVModelBundle\Entity\DataInterface;

/**
 * Overrides the base entity matcher to use options inside the admin configurations
 */
class AdminEntityMatcher extends BaseAdminEntityMatcher
{
    /**
     * @param mixed $entity
     *
     * @return Admin
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
