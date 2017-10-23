<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\ProcessBundle\Model\ProcessState;

/**
 * Use input as criteria to find EAV Data
 */
class EAVCriteriaReaderTask extends EAVReaderTask
{
    /**
     * @param ProcessState $state
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     *
     * @return array
     */
    protected function getOptions(ProcessState $state)
    {
        $options = parent::getOptions($state);
        $options['criteria'] = $state->getInput();
        $options['allow_reset'] = true;

        return $options;
    }
}
