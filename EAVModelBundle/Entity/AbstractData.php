<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\EAVModelBundle\Entity;

use CleverAge\EAVManager\UserBundle\Entity\AuthorableInterface;
use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Sidus\EAVModelBundle\Entity\AbstractData as AbstractBaseData;

/**
 * Adding authoring information on the Data entities.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
abstract class AbstractData extends AbstractBaseData implements AuthorableInterface
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="CleverAge\EAVManager\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id", onDelete="SET NULL")
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
     *
     * @return AbstractData
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
     *
     * @return AbstractData
     */
    public function setUpdatedBy(User $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        $this->createdAt = new \DateTime();
        $this->createdBy = null;
        $this->updatedAt = new \DateTime();
        $this->updatedBy = null;

        parent::__clone();
    }
}
