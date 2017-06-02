<?php

namespace CleverAge\EAVManager\ProcessBundle\Model;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Allow the task to configure it's options, set default basic options for errors handling
 */
abstract class AbstractConfigurableTask implements InitializableTaskInterface
{
    const STOP_ON_ERROR = 'stop_on_error';
    const LOG_ERRORS = 'log_errors';

    /**
     * Only validate the options at initialization, ensuring that the task will not fail at runtime
     *
     * @param ProcessState $processState
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function initialize(ProcessState $processState)
    {
        $this->getOptions($processState);
    }

    /**
     * @param ProcessState $processState
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     *
     * @return array
     */
    protected function getOptions(ProcessState $processState)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        return $resolver->resolve($processState->getTaskConfiguration()->getOptions());
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                self::STOP_ON_ERROR => true,
                self::LOG_ERRORS => true,
            ]
        );
        $resolver->setAllowedTypes(self::STOP_ON_ERROR, ['bool']);
        $resolver->setAllowedTypes(self::LOG_ERRORS, ['bool']);
    }
}
