<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @TODO describe class usage
 */
class DoctrinePersisterProcess implements ProcessInterface
{

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var array */
    protected $data;

    /**
     * DoctrinePersisterProcess constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->manager->beginTransaction();
        foreach ($this->data as $key => $item) {
            $this->manager->persist($item);
        }

        $this->manager->flush();
        $this->manager->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->data;
    }

}
