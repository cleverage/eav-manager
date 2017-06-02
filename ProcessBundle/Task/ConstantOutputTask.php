<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use CleverAge\EAVManager\ProcessBundle\Model\AbstractConfigurableTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Always send the same output regardless of the input
 */
class ConstantOutputTask extends AbstractConfigurableTask
{
    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'output',
        ]);
    }

    /**
     * @param ProcessState $processState
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function execute(ProcessState $processState)
    {
        $options = $this->getOptions($processState);
        $processState->setOutput($options['output']);
    }
}
