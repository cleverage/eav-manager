<?php

namespace CleverAge\EAVManager\ImportBundle\Exception;

/**
 * Exception thrown when a transform method of a Transformer cannot be implemented (especially for one way transformations)
 */
class TransformerNotImplementedException extends \UnexpectedValueException
{
    /**
     * @param string $methodName
     *
     * @return TransformerNotImplementedException
     */
    public static function create(string $methodName): TransformerNotImplementedException
    {
        return new TransformerNotImplementedException("Transformation not implemented : {$methodName}");
    }
}
