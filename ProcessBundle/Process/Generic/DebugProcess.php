<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;

/**
 * @TODO describe class usage
 */
class DebugProcess implements ProcessInterface
{
    protected $data;

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
        dump($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->data;
    }

}
