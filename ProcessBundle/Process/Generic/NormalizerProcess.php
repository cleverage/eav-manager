<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @TODO describe class usage ; find a better name ?
 */
class NormalizerProcess implements ProcessInterface
{
    /** @var NormalizerInterface|DenormalizerInterface */
    protected $serializer;

    /** @var array */
    protected $format;

    /** @var array */
    protected $context;

    /** @var string */
    protected $denormalizeClass;

    /** @var array */
    protected $dataToProcess;

    /** @var array */
    protected $proceededData = [];

    /**
     * @param DenormalizerInterface|NormalizerInterface $serializer
     * @param array                                     $format
     * @param array                                     $context
     * @param string                                    $denormalizeClass
     */
    public function __construct($serializer, $format = [], array $context = [], $denormalizeClass = null)
    {
        $this->serializer = $serializer;
        $this->format = $format;
        $this->context = $context;
        $this->denormalizeClass = $denormalizeClass;
    }


    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        $this->dataToProcess = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        foreach ($this->dataToProcess as $key => $item) {
            if (!$this->denormalizeClass) {
                $this->proceededData[$key] = $this->serializer->normalize($item, $this->format, $this->context);
            } else {
                $this->proceededData[$key] = $this->serializer->denormalize(
                    $item,
                    $this->denormalizeClass,
                    $this->format,
                    $this->context
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->proceededData;
    }
}
