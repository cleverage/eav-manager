<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;
use Sidus\EAVModelBundle\Entity\DataInterface;

/**
 * @TODO describe class usage
 */
class EAVDataSetterProcess implements ProcessInterface
{

    /** @var array */
    protected $attributeValues;

    /** @var DataInterface[] */
    protected $data;

    /**
     * EAVDataSetterProcess constructor.
     *
     * @param array $attributeValues
     */
    public function __construct(array $attributeValues)
    {
        $this->attributeValues = $attributeValues;
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
        foreach ($this->attributeValues as $attribute => $value) {
            foreach ($this->data as $item) {
                $item->set($attribute, $value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->data;
    }
}
