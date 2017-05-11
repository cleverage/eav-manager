<?php

namespace CleverAge\EAVManager\ImportBundle\Exception;

use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Thrown when a reference is missing during an import
 */
class NonUniqueReferenceException extends \UnexpectedValueException
{
    /**
     * @param FamilyInterface $family
     * @param mixed           $reference
     * @param \Exception      $e
     *
     * @return NonUniqueReferenceException
     */
    public static function create(
        FamilyInterface $family,
        $reference,
        \Exception $e = null
    ): NonUniqueReferenceException
    {
        $m = "Non-unique result exception for family {$family->getCode()} and reference {$reference}";

        return new self($m, 0, $e);
    }
}
