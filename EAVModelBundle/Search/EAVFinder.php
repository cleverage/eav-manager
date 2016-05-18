<?php

namespace CleverAge\EAVManager\EAVModelBundle\Search;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\LessThan1MaxPerPageException;
use Pagerfanta\Exception\NotIntegerCurrentPageException;
use Pagerfanta\Exception\NotIntegerMaxPerPageException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sidus\EAVFilterBundle\Configuration\EAVElasticaFilterConfigurationHandler;
use Sidus\EAVFilterBundle\DependencyInjection\Configuration;
use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Filter\FilterFactory;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Use this service as a wrapper of the EAVFilterBundle API to find data based on attributes values
 */
class EAVFinder
{
    /** @var Registry */
    protected $doctrine;

    /** @var FilterFactory */
    protected $filterFactory;

    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /**
     * @param Registry                   $doctrine
     * @param FilterFactory              $filterFactory
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     * @param FormFactoryInterface       $formFactory
     */
    public function __construct(
        Registry $doctrine,
        FilterFactory $filterFactory,
        FamilyConfigurationHandler $familyConfigurationHandler,
        FormFactoryInterface $formFactory
    ) {
        $this->doctrine = $doctrine;
        $this->filterFactory = $filterFactory;
        $this->familyConfigurationHandler = $familyConfigurationHandler;
        $this->formFactory = $formFactory;
    }

    /**
     * @param FamilyInterface $family
     * @param array           $filterBy
     * @param array           $orderBy
     * @return Pagerfanta
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws AlreadySubmittedException
     * @throws InvalidOptionsException
     * @throws \OutOfBoundsException
     * @throws OutOfRangeCurrentPageException
     * @throws NotIntegerMaxPerPageException
     * @throws LessThan1CurrentPageException
     * @throws NotIntegerCurrentPageException
     * @throws LessThan1MaxPerPageException
     */
    public function findBy(FamilyInterface $family, array $filterBy, array $orderBy = [])
    {
        $filterConfigurationHandler = $this->buildConfigurationHandler($family, $filterBy, $orderBy);
        $this->handleArray($filterConfigurationHandler, $filterBy, $orderBy);

        return $filterConfigurationHandler->getPager();
    }

    /**
     * @param string $code
     * @param array  $config
     * @return EAVElasticaFilterConfigurationHandler
     * @throws \UnexpectedValueException
     */
    public function createFilterConfigurationHandler($code, array $config)
    {
        return new EAVElasticaFilterConfigurationHandler(
            $code,
            $this->doctrine,
            $this->filterFactory,
            $config,
            $this->familyConfigurationHandler
        );
    }

    /**
     * @param FamilyInterface $family
     * @param array           $filterBy
     * @param array           $orderBy
     * @return EAVElasticaFilterConfigurationHandler
     * @throws \UnexpectedValueException
     */
    protected function buildConfigurationHandler(FamilyInterface $family, array $filterBy, array $orderBy = [])
    {
        $code = uniqid(); // Not important

        $sortable = [];
        foreach ($orderBy as $property => $direction) {
            $sortable[] = $property;
        }

        $fields = [];
        foreach ($filterBy as $property => $value) {
            $config = null;
            if ($family->hasAttribute($property)) {
                $attribute = $family->getAttribute($property);
                if ($attribute->getType()->isRelation()) {
                    $config = [
                        'type' => 'autocomplete_data',
                    ];
                }
            }
            $fields[$property] = $config;
        }

        // Build fake configuration on the fly
        $config = [
            'sidus_eav_filter' => [
                'configurations' => [
                    $code => [
                        'family' => $family->getCode(),
                        'sortable' => $sortable,
                        'fields' => $fields,
                    ],
                ],
            ],
        ];

        $configuration = new Configuration('sidus_eav_filter');
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $config);

        return $this->createFilterConfigurationHandler($code, $config['configurations'][$code]);
    }

    /**
     * @param EAVElasticaFilterConfigurationHandler $filterConfigurationHandler
     * @param array                                 $filterBy
     * @param array                                 $orderBy
     * @throws \OutOfBoundsException
     * @throws LessThan1MaxPerPageException
     * @throws NotIntegerCurrentPageException
     * @throws LessThan1CurrentPageException
     * @throws NotIntegerMaxPerPageException
     * @throws OutOfRangeCurrentPageException
     * @throws AlreadySubmittedException
     * @throws \LogicException
     * @throws InvalidOptionsException
     */
    protected function handleArray(
        EAVElasticaFilterConfigurationHandler $filterConfigurationHandler,
        array $filterBy,
        array $orderBy = []
    ) {
        $filterConfigurationHandler->buildForm($this->formFactory->createBuilder());
        $filterConfigurationHandler->handleArray([
            EAVElasticaFilterConfigurationHandler::FILTERS_FORM_NAME => $filterBy,
            EAVElasticaFilterConfigurationHandler::SORTABLE_FORM_NAME => $orderBy, // Not working for the moment
        ]);
    }
}
