<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use CleverAge\EAVManager\ProcessBundle\Model\AbstractConfigurableTask;
use Psr\Log\LogLevel;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Accepts an object or an array as input and sets values from configuration
 */
class PropertySetterTask extends AbstractConfigurableTask
{
    /** @var PropertyAccessorInterface */
    protected $accessor;

    /**
     * @param PropertyAccessorInterface $accessor
     */
    public function __construct(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

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
            'values',
        ]);
        $resolver->setAllowedTypes('values', ['array']);
    }

    /**
     * @param ProcessState $processState
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function execute(ProcessState $processState)
    {
        $options = $this->getOptions($processState);
        /** @noinspection ForeachSourceInspection */
        foreach ($options['values'] as $key => $value) {
            try {
                $this->accessor->setValue($processState->getInput(), $key, $value);
            } catch (\Exception $e) {
                if ($options[AbstractConfigurableTask::LOG_ERRORS]) {
                    $processState->log($e->getMessage(), LogLevel::ERROR, $key, [
                        'value' => $value,
                    ]);
                }
                if ($options[AbstractConfigurableTask::STOP_ON_ERROR]) {
                    $processState->stop($e);

                    return;
                }
            }
        }

        $processState->setOutput($processState->getInput());
    }
}
