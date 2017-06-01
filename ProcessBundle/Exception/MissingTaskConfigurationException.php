<?php

namespace CleverAge\EAVManager\ProcessBundle\Exception;

use ProcessBundle\Exception\ProcessExceptionInterface;

/**
 * Exception thrown when trying to fetch a missing task configuration
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
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
