<?php

namespace CleverAge\EAVManager\EAVModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sidus\EAVModelBundle\Entity\Data as BaseData;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\PublishingBundle\Entity\PublishableInterface;
use CleverAge\EAVManager\UserBundle\Entity\AuthorableInterface;
use CleverAge\EAVManager\UserBundle\Entity\User;
use JMS\Serializer\Annotation as JMS;

abstract class Data extends BaseData implements AuthorableInterface, PublishableInterface
{
    /**
     * @var string
     * @ORM\Column(name="mongo_id", type="string", length=25)
     * @JMS\Accessor(getter="getMongoId")
     */
    protected $mongoId;

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
     * @inheritDoc
     */
    public function __construct(FamilyInterface $family)
    {
        parent::__construct($family);
        $this->mongoId = (string) new \MongoId();
    }

    /**
     * @return string
     */
    public function getMongoId()
    {
        if (!$this->mongoId) {
            $this->mongoId = (string) new \MongoId();
        }
        return $this->mongoId;
    }

    /**
     * @inheritDoc
     */
    public function getPublicationUuid()
    {
        return $this->getMongoId();
    }

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
