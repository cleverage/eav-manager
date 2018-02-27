<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
