<?php

namespace CleverAge\EAVManager\UserBundle\Entity;

use CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Sidus\EAVModelBundle\Entity\DataInterface;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="eavmanager_user")
 */
class User implements UserInterface, \Serializable
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $salt;

    /**
     * Date de demande de changement de mot de passe de l'utilisateur
     *
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * Token utilisé pour la génération de lien de connexion:
     * - Si l'utilisateur vient d'être créé
     * - Si l'utilisateur a perdu son mot de passe
     *
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $authenticationToken;

    /**
     * Définit si l'utilisateur est nouveau ou non pour l'envoi de l'email
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $new = true;

    /**
     * Defined the user as active, meaning the account is usable
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $enabled = true;

    /**
     * Conserve l'information de l'email envoyé pour ne pas dupliquer les emails
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $emailSent = false;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    protected $roles = [];

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
     * @ORM\OneToMany(targetEntity="CleverAge\EAVManager\SecurityBundle\Entity\FamilyPermission", mappedBy="user",
     *                                                           cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $familyPermissions;

    /**
     * @var DataInterface
     * @ORM\OneToOne(targetEntity="Sidus\EAVModelBundle\Entity\DataInterface", cascade={"persist"})
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

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->passwordRequestedAt = new DateTime();
        $this->salt = bin2hex(random_bytes(32));
        $this->resetAuthenticationToken();

        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->familyPermissions = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param DateTime $passwordRequestedAt
     *
     * @return User
     */
    public function setPasswordRequestedAt(DateTime $passwordRequestedAt = null)
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
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
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @param string $role
     *
     * @return User
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
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
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    /**
     * @param bool $boolean
     *
     * @return $this
     */
    public function setSuperAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return string|null
     */
    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }

    /**
     * @return User
     */
    public function resetAuthenticationToken()
    {
        $this->authenticationToken = bin2hex(random_bytes(64));

        return $this;
    }

    /**
     * @return User
     */
    public function unsetAuthenticationToken()
    {
        $this->authenticationToken = null;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * @param boolean $new
     *
     * @return User
     */
    public function setNew($new)
    {
        $this->new = $new;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEmailSent()
    {
        return $this->emailSent;
    }

    /**
     * @param boolean $emailSent
     *
     * @return User
     */
    public function setEmailSent($emailSent)
    {
        $this->emailSent = $emailSent;

        return $this;
    }

    /**
     * @return Group[]|Collection
     */
    public function getGroups()
    {
        return $this->groups;
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
     *
     * @return User
     */
    public function setRawRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param Group $group
     *
     * @return User
     */
    public function addGroup(Group $group)
    {
        $this->groups->add($group);

        return $this;
    }

    /**
     * @param Group $group
     *
     * @return User
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * @return User
     */
    public function clearGroups()
    {
        $this->groups->clear();

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
     *
     * @return User
     */
    public function addFamilyPermission(FamilyPermission $familyPermission)
    {
        $familyPermission->setUser($this);

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
     *
     * @return User
     */
    public function setData(DataInterface $data = null)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * String representation of object
     *
     * @see https://goo.gl/zds3Tm (Le lien était trop long)
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->username,
                $this->password,
                $this->salt,
                $this->roles,
            ]
        );
    }

    /**
     * Constructs the object
     *
     * @param string $serialized The string representation of the object.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->roles,
            ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
    }
}
