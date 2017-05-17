<?php

namespace CleverAge\EAVManager\SecurityBundle\Security\Core\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Used to work with roles and permissions.
 */
class LeafRole extends Role
{
    /** @var LeafRole */
    protected $parent;

    /** @var LeafRole[]|Collection */
    protected $children;

    /**
     * @param string $role The role name
     */
    public function __construct($role)
    {
        parent::__construct($role);
        $this->children = new ArrayCollection();
    }

    /**
     * @return LeafRole
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param LeafRole $parent
     *
     * @return LeafRole
     */
    public function setParent(LeafRole $parent = null)
    {
        if (!$parent->getChildren()->contains($this)) {
            $parent->addChild($this);
        }
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|LeafRole[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param LeafRole $child
     *
     * @return LeafRole
     */
    public function addChild(LeafRole $child)
    {
        $this->children->add($child);
        $child->setParent($this);

        return $this;
    }

    /**
     * @param LeafRole $child
     *
     * @return LeafRole
     */
    public function removeChild(LeafRole $child)
    {
        $this->children->remove($child);
        $child->setParent(null);

        return $this;
    }

    /**
     * @param Collection|LeafRole[] $children
     *
     * @return LeafRole
     */
    public function setChildren($children)
    {
        $this->clearChildren();
        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * @return LeafRole
     */
    public function clearChildren()
    {
        foreach ($this->children as $child) {
            $this->removeChild($child);
        }

        return $this;
    }
}
