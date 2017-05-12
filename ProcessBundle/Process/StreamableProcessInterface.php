<?php

namespace CleverAge\EAVManager\ProcessBundle\Process;

/**
 * @TODO describe class usage
 */
interface StreamableProcessInterface extends ProcessInterface
{
    /**
     * @return bool
     */
    public function isOver();
}
