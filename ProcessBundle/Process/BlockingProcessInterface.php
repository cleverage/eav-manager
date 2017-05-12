<?php

namespace CleverAge\EAVManager\ProcessBundle\Process;

interface BlockingProcessInterface extends ProcessInterface
{
    /**
     *
     */
    public function finalize();
}
