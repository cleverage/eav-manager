<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\SecurityBundle\Entity;

use CleverAge\EAVManager\UserBundle\Entity\Group;
use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @ORM\Entity(repositoryClass="FamilyPermissionRepository")
 * @ORM\Table(name="eavmanager_family_permission")
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class FamilyPermission
{
    /** @var array */
    protected static $permissions = [
        'list',
        'read',
        'create',
        'edit',
        'delete',
        'publish',
    ];

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\UserBundle\Entity\User", inversedBy="familyPermissions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $user;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\UserBundle\Entity\Group", inversedBy="familyPermissions")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $group;

    /**
     * @var FamilyInterface
     *
     * @ORM\Column(name="family_code", type="sidus_family")
     */
    protected $family;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $list = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="`read`") Reserved SQL word
     */
    protected $read = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="`create`") Reserved SQL word
     */
    protected $create = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $edit = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="`delete`") Reserved SQL word
     */
    protected $delete = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $publish = true;

    /**
     * @param string $permission
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function hasPermission($permission)
    {
        if (!\in_array($permission, $this::$permissions, true)) {
            throw new \UnexpectedValueException("Permissions does not exists: {$permission}");
        }
        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($this, $permission);
    }

    /**
     * @return array
     */
    public static function getPermissions()
    {
        return self::$permissions;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return FamilyPermission
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return FamilyPermission
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
        if ($user && !$user->getFamilyPermissions()->contains($this)) {
            $user->getFamilyPermissions()->add($this);
        }

        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     *
     * @return FamilyPermission
     */
    public function setGroup(Group $group = null)
    {
        $this->group = $group;
        if ($group && !$group->getFamilyPermissions()->contains($this)) {
            $group->getFamilyPermissions()->add($this);
        }

        return $this;
    }

    /**
     * @return FamilyInterface
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param FamilyInterface $family
     *
     * @return FamilyPermission
     */
    public function setFamily(FamilyInterface $family = null)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * @return bool
     */
    public function isList()
    {
        return $this->list;
    }

    /**
     * @param bool $list
     *
     * @return FamilyPermission
     */
    public function setList($list)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        return $this->read;
    }

    /**
     * @param bool $read
     *
     * @return FamilyPermission
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreate()
    {
        return $this->create;
    }

    /**
     * @param bool $create
     *
     * @return FamilyPermission
     */
    public function setCreate($create)
    {
        $this->create = $create;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEdit()
    {
        return $this->edit;
    }

    /**
     * @param bool $edit
     *
     * @return FamilyPermission
     */
    public function setEdit($edit)
    {
        $this->edit = $edit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->delete;
    }

    /**
     * @param bool $delete
     *
     * @return FamilyPermission
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublish()
    {
        return $this->publish;
    }

    /**
     * @param bool $publish
     *
     * @return FamilyPermission
     */
    public function setPublish($publish)
    {
        $this->publish = $publish;

        return $this;
    }
}
