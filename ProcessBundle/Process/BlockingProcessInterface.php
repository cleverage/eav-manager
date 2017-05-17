<?php

namespace CleverAge\EAVManager\ProcessBundle\Process;

interface BlockingProcessInterface extends ProcessInterface
{
    /**
     * @todo comment
     */
    public function finalize();
}
