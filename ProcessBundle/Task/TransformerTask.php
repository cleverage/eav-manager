<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use CleverAge\EAVManager\ProcessBundle\Model\AbstractConfigurableTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransformerTask extends AbstractConfigurableTask
{
    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired([
            'mapping',
        ]);
        $resolver->setAllowedTypes('mapping', ['array']);
        $resolver->setDefaults([
            'ignore_missing' => false,
            'ignore_extra' => false,
        ]);
        $resolver->setAllowedTypes('ignore_missing', ['bool']);
        $resolver->setAllowedTypes('ignore_extra', ['bool']);
    }

    /**
     * @param ProcessState $processState
     */
    public function execute(ProcessState $processState)
    {
        // @todo
    }
}
