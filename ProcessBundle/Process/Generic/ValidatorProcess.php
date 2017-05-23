<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @TODO describe class usage
 */
class ValidatorProcess implements ProcessInterface
{

    /** @var ValidatorInterface */
    protected $validator;

    /** @var array */
    protected $data;

    /**
     * ValidatorProcess constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }


    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        foreach ($this->data as $key => $item) {
            $violations = $this->validator->validate($item);
            $countErrors = count($violations);
            if ($countErrors) {
                //TODO better error handling
                throw new \UnexpectedValueException(
                    "Error during validation of item '{$key}' ({$countErrors} violations)"
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->data;
    }

}
