<?php

namespace CleverAge\EAVManager\SecurityBundle\Security\Core\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;
use Symfony\Component\Validator\Constraints\Collection;

class RoleHierarchy extends BaseRoleHierarchy
{
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
                $flatRoles[$rootRole] =  new LeafRole($rootRole);
            }
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
