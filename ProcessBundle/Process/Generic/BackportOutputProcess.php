<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;

/**
 * @TODO describe class usage
 */
class BackportOutputProcess implements ProcessInterface
{

    /** @var ProcessInterface */
    protected $previousProcess;

    /**
     * BackportOutputProcess constructor.
     *
     * @param ProcessInterface $previousProcess
     */
    public function __construct(ProcessInterface $previousProcess)
    {
        $this->previousProcess = $previousProcess;
    }


    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->previousProcess->getOutput();
    }

}
