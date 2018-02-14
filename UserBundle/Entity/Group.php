<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Entity;

use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="eavmanager_group")
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class Group
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var FamilyPermission[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission", mappedBy="group",
     *                                                           cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $familyPermissions;

    /**
     * @var User[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="CleverAge\EAVManager\UserBundle\Entity\User", mappedBy="groups")
     */
    protected $users;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    protected $roles = [];

    /**
     * Building default values.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->familyPermissions = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     *
     * @return Group
     */
    public function setUpdatedAt(DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return FamilyPermission[]|Collection
     */
    public function getFamilyPermissions()
    {
        return $this->familyPermissions;
    }

    /**
     * @param FamilyPermission $familyPermission
     *
     * @return Group
     */
    public function addFamilyPermission(FamilyPermission $familyPermission)
    {
        $familyPermission->setGroup($this);

        return $this;
    }

    /**
     * @param FamilyPermission $familyPermission
     *
     * @return $this
     */
    public function removeFamilyPermission(FamilyPermission $familyPermission)
    {
        $this->familyPermissions->removeElement($familyPermission);
        $familyPermission->setGroup(null);

        return $this;
    }

    /**
     * Returns the user roles.
     *
     * @return array The roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return \in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @param string $role
     *
     * @return Group
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if (User::ROLE_DEFAULT === $role) {
            return $this;
        }

        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param string $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        $key = array_search(strtoupper($role), $this->roles, true);
        if (false !== $key) {
            /** @var string $key */
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }
}
