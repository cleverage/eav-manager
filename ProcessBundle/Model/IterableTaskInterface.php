<?php

namespace CleverAge\EAVManager\ProcessBundle\Model;

/**
 * Allow the task to
 */
interface IterableTaskInterface extends TaskInterface
{
    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration
     *
     * @return bool
     */
    public function next();
}
