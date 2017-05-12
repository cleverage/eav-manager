<?php

namespace CleverAge\EAVManager\ProcessBundle\Process;

/**
 * @TODO describe class usage
 */
class StaticOutputProcess implements ProcessInterface
{
    protected $output;

    /**
     * StaticOutputProcess constructor.
     *
     * @param mixed $output
     */
    public function __construct($output)
    {
        $this->output = $output;
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
        return $this->output;
    }
}
