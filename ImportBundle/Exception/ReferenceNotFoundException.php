<?php

namespace CleverAge\EAVManager\ImportBundle\Exception;

use Sidus\EAVModelBundle\Model\FamilyInterface;

/**
 * Thrown when a reference is missing during an import
 */
class ReferenceNotFoundException extends \UnexpectedValueException
{
    /**
     * @param FamilyInterface $family
     * @param mixed           $reference
     * @param \Exception      $e
     *
     * @return ReferenceNotFoundException
     */
    public static function create(FamilyInterface $family, $reference, \Exception $e = null): ReferenceNotFoundException
    {
        $m = "Reference not found {$reference} for family {$family->getCode()}";

        return new self($m, 0, $e);
    }
}
