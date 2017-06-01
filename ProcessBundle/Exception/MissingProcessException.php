<?php

namespace CleverAge\EAVManager\ProcessBundle\Exception;

use ProcessBundle\Exception\ProcessExceptionInterface;

/**
 * Exception thrown when trying to fetch a missing process
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class MissingProcessException extends \UnexpectedValueException implements ProcessExceptionInterface
{
    /**
     * @param string $code
     */
    public function __construct($code)
    {
        parent::__construct("No process with code : {$code}");
    }
}
