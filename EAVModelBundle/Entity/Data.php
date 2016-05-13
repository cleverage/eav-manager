<?php

namespace CleverAge\EAVManager\EAVModelBundle\Entity;

use CleverAge\EAVManager\UserBundle\Entity\AuthorableInterface;
use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Sidus\EAVModelBundle\Entity\Data as BaseData;

abstract class Data extends BaseData implements AuthorableInterface
{
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
     * @JMS\Exclude()
     */
    protected $createdBy;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id", onDelete="SET NULL")
     * @JMS\Exclude()
     */
    protected $updatedBy;

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     * @return Data
     */
    public function setCreatedBy(User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param User $updatedBy
     * @return Data
     */
    public function setUpdatedBy(User $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}
