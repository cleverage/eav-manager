<?php

namespace CleverAge\EAVManager\ProcessBundle\Exception;

/**
 * Exception thrown when trying to fetch a missing task configuration
 */
class MissingTaskConfigurationException extends \UnexpectedValueException implements ProcessExceptionInterface
{
    /**
     * @param string $code
     */
    public function __construct($code)
    {
        parent::__construct("No task configuration with code : {$code}");
    }
}
