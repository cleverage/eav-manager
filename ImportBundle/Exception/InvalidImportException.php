<?php

namespace CleverAge\EAVManager\ImportBundle\Exception;


/**
 * Represent a single error for a row during import
 */
class InvalidImportException extends \UnexpectedValueException
{

    /**
     * @param string $message
     *
     * @return InvalidImportException
     */
    public static function create($message)
    {
        return new InvalidImportException($message);
    }
}
