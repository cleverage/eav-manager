<?php

namespace CleverAge\EAVManager\ProcessBundle\DataSource;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;
use Sidus\EAVModelBundle\Doctrine\EAVFinder;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;

/**
 * @TODO describe class usage
 * TODO : implement StreamableProcessInterface
 */
class EAVDataSourceProcess implements ProcessInterface
{

    /** @var EAVFinder */
    protected $eavFinder;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var string */
    protected $familyCode;

    /** @var DataInterface[] */
    protected $eavEntities;

    /** @var array */
    protected $filterBy = [];

    /**
     * EAVDataSourceProcess constructor.
     *
     * @param EAVFinder      $eavFinder
     * @param FamilyRegistry $familyRegistry
     */
    public function __construct(EAVFinder $eavFinder, FamilyRegistry $familyRegistry)
    {
        $this->eavFinder = $eavFinder;
        $this->familyRegistry = $familyRegistry;
    }


    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        if ($data) {
            $this->familyCode = $data;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $family = $this->familyRegistry->getFamily($this->familyCode);
        $this->eavEntities = $this->eavFinder->findBy($family, $this->filterBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->eavEntities;
    }

    /**
     * @param array $filterBy
     */
    public function setFilterBy(array $filterBy)
    {
        $this->filterBy = $filterBy;
    }
}
