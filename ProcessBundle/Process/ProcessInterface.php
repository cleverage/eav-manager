<?php

namespace CleverAge\EAVManager\ProcessBundle\Process;

interface ProcessInterface
{
    /**
     * @param mixed $data
     */
    public function setInput($data);

    public function execute();

    /**
     * @return mixed
     */
    public function getOutput();
}
