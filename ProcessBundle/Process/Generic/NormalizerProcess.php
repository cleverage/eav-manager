<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;
use Nelmio\Alice\FixtureBuilder\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @TODO describe class usage ; find a better name ?
 */
class NormalizerProcess implements ProcessInterface
{
    /** @var NormalizerInterface|DenormalizerInterface */
    protected $serializer;

    /** @var bool */
    protected $doNormalize;

    /** @var array */
    protected $dataToProcess;

    /** @var array */
    protected $proceededData = [];

    /**
     * @param DenormalizerInterface|NormalizerInterface $serializer
     * @param bool                                      $doNormalize
     */
    public function __construct($serializer, $doNormalize = true)
    {
        $this->serializer = $serializer;
        $this->doNormalize = $doNormalize;
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
        foreach ($this->dataToProcess as $item) {
            if ($this->doNormalize) {
                $this->proceededData[] = $this->serializer->normalize($item);
            } else {
                $this->proceededData[] = $this->serializer->denormalize($item);
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
