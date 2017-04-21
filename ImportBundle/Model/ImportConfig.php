<?php

namespace CleverAge\EAVManager\ImportBundle\Model;

use CleverAge\EAVManager\ImportBundle\Import\EAVDataImporter;
use CleverAge\EAVManager\ImportBundle\Source\DataSourceInterface;
use CleverAge\EAVManager\ImportBundle\Transformer\EAVDataTransformerInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Handle import configuration
 */
class ImportConfig
{
    /** @var string */
    protected $code;

    /** @var FamilyInterface */
    protected $family;

    /** @var EAVDataImporter */
    protected $service;

    /** @var EAVDataTransformerInterface */
    protected $transformer;

    /** @var array */
    protected $mapping;

    /** @var array */
    protected $context;

    /** @var array */
    protected $options;

    /** @var DataSourceInterface */
    protected $source;

    /**
     * ImportConfig constructor.
     *
     * @param string         $code
     * @param FamilyRegistry $familyRegistry
     * @param array          $configuration
     *
     * @throws MissingFamilyException
     * @throws AccessException
     * @throws InvalidArgumentException
     * @throws UnexpectedTypeException
     */
    public function __construct($code, FamilyRegistry $familyRegistry, $configuration)
    {
        $this->code = $code;

        $this->family = $familyRegistry->getFamily($configuration['family']);
        unset($configuration['family']);

        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($configuration as $key => $value) {
            $accessor->setValue($this, $key, $value);
        }
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return FamilyInterface
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param FamilyInterface $family
     */
    public function setFamily(FamilyInterface $family)
    {
        $this->family = $family;
    }

    /**
     * @return EAVDataImporter
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param EAVDataImporter $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return EAVDataTransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param EAVDataTransformerInterface $transformer
     */
    public function setTransformer(EAVDataTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param array $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $code
     * @param null   $default
     *
     * @return mixed
     */
    public function getOption($code, $default = null)
    {
        if (!isset($this->options[$code])) {
            return $default;
        }

        return $this->options[$code];
    }

    /**
     * @param string $code
     * @param mixed  $value
     */
    public function addOption($code, $value)
    {
        $this->options[$code] = $value;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param string $code
     *
     * @return bool|mixed
     */
    public function getAttributeMapping($code)
    {
        if (array_key_exists($code, $this->mapping)) {
            return $this->mapping[$code];
        }

        return false;
    }

    /**
     * @return DataSourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param DataSourceInterface $source
     */
    public function setSource(DataSourceInterface $source)
    {
        $this->source = $source;
    }

}
