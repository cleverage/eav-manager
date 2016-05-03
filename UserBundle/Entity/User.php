<?php

namespace CleverAge\EAVManager\UserBundle\Entity;

use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use JMS\Serializer\Annotation as JMS;
use Sidus\EAVModelBundle\Entity\DataInterface;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="eavmanager_user")
 * @JMS\ExclusionPolicy("all")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
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
     * @ORM\OneToMany(targetEntity="CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $familyPermissions;

    /**
     * @var DataInterface
     * @ORM\OneToOne(targetEntity="Sidus\EAVModelBundle\Entity\DataInterface", cascade={"persist"})
     * @JMS\Expose()
     */
    protected $data;

    /**
     * @ORM\ManyToMany(targetEntity="CleverAge\EAVManager\UserBundle\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(name="eavmanager_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    public function __construct()
    {
        parent::__construct();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->familyPermissions = new ArrayCollection();
        $this->plainPassword = sha1(uniqid('password generator', true));
        $this->groups = new ArrayCollection();
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
    public function getCombinedFamilyPermissions()
    {
        $permissions = new ArrayCollection($this->getFamilyPermissions()->toArray());
        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            foreach ($group->getFamilyPermissions() as $familyPermission) {
                $permissions->add($familyPermission);
            }
        }

        return $permissions;
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
        $familyPermission->setUser($this);

        return $this;
    }

    /**
     * @param FamilyPermission $familyPermission
     * @return $this
     */
    public function removeFamilyPermission(FamilyPermission $familyPermission)
    {
        $this->familyPermissions->removeElement($familyPermission);
        $familyPermission->setUser(null);

        return $this;
    }

    /**
     * @return DataInterface
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param DataInterface $data
     * @return User
     */
    public function setData(DataInterface $data = null)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRawRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the user roles
     *
     * @param array $roles
     * @return User
     */
    public function setRawRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }
}
