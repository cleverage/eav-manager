<?php

namespace CleverAge\EAVManager\UserBundle\Entity;

use FOS\UserBundle\Entity\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="eavmanager_group")
 * @JMS\ExclusionPolicy("all")
 */
class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var DateTime
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var FamilyPermission[]|Collection
     * @ORM\OneToMany(targetEntity="CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission", mappedBy="group", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $familyPermissions;

    /**
     * @var User[]|Collection
     * @ORM\ManyToMany(targetEntity="CleverAge\EAVManager\UserBundle\Entity\User", mappedBy="groups")
     */
    protected $users;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->familyPermissions = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->roles = [];
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
     * @return User
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
     * @return User
     */
    public function addFamilyPermission(FamilyPermission $familyPermission)
    {
        $familyPermission->setGroup($this);
        return $this;
    }

    /**
     * @param FamilyPermission $familyPermission
     * @return $this
     */
    public function removeFamilyPermission(FamilyPermission $familyPermission)
    {
        $this->familyPermissions->removeElement($familyPermission);
        $familyPermission->setGroup(null);
        return $this;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }
}
