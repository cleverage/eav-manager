<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use CleverAge\EAVManager\ProcessBundle\Model\AbstractConfigurableTask;
use Psr\Log\LogLevel;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate the input and pass it to the output
 */
class ValidatorTask extends AbstractConfigurableTask
{
    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param ProcessState $processState
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @throws \UnexpectedValueException
     */
    public function execute(ProcessState $processState)
    {
        $violations = $this->validator->validate($processState->getInput());
        $options = $this->getOptions($processState);

        if ($options[AbstractConfigurableTask::LOG_ERRORS]) {
            /** @var  $violation ConstraintViolationInterface */
            foreach ($violations as $violation) {
                $invalidValue = $violation->getInvalidValue();
                $processState->log(
                    $violation->getMessage(),
                    LogLevel::ERROR,
                    $violation->getPropertyPath(),
                    [
                        'code' => $violation->getCode(),
                        'invalid_value' => is_object($invalidValue) ? get_class($invalidValue) : $invalidValue,
                    ]
                );
            }
        }

        if ($options[AbstractConfigurableTask::STOP_ON_ERROR] && 0 < $violations->count()) {
            $processState->stop(
                new \UnexpectedValueException("{$violations->count()} constraint violations detected on validation")
            );
        }

        $processState->setOutput($processState->getInput());
    }
}
