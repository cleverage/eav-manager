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
     * Switching to string because the system crashed when a family was removed and there is no way to handle this
     *
     * @var string|null
     *
     * @ORM\Column(name="family_code", type="string")
     */
    protected $familyCode;

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
    public function hasPermission($permission): bool
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
    public static function getPermissions(): array
    {
        return self::$permissions;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return FamilyPermission
     */
    public function setId($id): FamilyPermission
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return FamilyPermission
     */
    public function setUser(User $user = null): FamilyPermission
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
    public function getGroup(): Group
    {
        return $this->group;
    }

    /**
     * @param Group $group
     *
     * @return FamilyPermission
     */
    public function setGroup(Group $group = null): FamilyPermission
    {
        $this->group = $group;
        if ($group && !$group->getFamilyPermissions()->contains($this)) {
            $group->getFamilyPermissions()->add($this);
        }

        return $this;
    }

    /**
     * @throws \LogicException
     */
    public function getFamily(): void
    {
        throw new \LogicException('Deprecated call, please use getFamilyCode instead');
    }

    /**
     * @param FamilyInterface $family
     *
     * @return FamilyPermission
     */
    public function setFamily(FamilyInterface $family = null): FamilyPermission
    {
        $this->familyCode = $family ? $family->getCode() : null;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFamilyCode(): ?string
    {
        return $this->familyCode;
    }

    /**
     * @param string $familyCode
     */
    public function setFamilyCode(string $familyCode = null): void
    {
        $this->familyCode = $familyCode;
    }

    /**
     * @return bool
     */
    public function isList(): bool
    {
        return $this->list;
    }

    /**
     * @param bool $list
     *
     * @return FamilyPermission
     */
    public function setList($list): FamilyPermission
    {
        $this->list = $list;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read;
    }

    /**
     * @param bool $read
     *
     * @return FamilyPermission
     */
    public function setRead($read): FamilyPermission
    {
        $this->read = $read;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreate(): bool
    {
        return $this->create;
    }

    /**
     * @param bool $create
     *
     * @return FamilyPermission
     */
    public function setCreate($create): FamilyPermission
    {
        $this->create = $create;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEdit(): bool
    {
        return $this->edit;
    }

    /**
     * @param bool $edit
     *
     * @return FamilyPermission
     */
    public function setEdit($edit): FamilyPermission
    {
        $this->edit = $edit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->delete;
    }

    /**
     * @param bool $delete
     *
     * @return FamilyPermission
     */
    public function setDelete($delete): FamilyPermission
    {
        $this->delete = $delete;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublish(): bool
    {
        return $this->publish;
    }

    /**
     * @param bool $publish
     *
     * @return FamilyPermission
     */
    public function setPublish($publish): FamilyPermission
    {
        $this->publish = $publish;

        return $this;
    }
}
