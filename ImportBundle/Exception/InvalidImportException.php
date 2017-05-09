<?php

namespace CleverAge\EAVManager\ImportBundle\Exception;

use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * Represent a single error for a row during import
 * @TODO : refator without using the constructor
 */
class InvalidImportException extends \UnexpectedValueException implements \JsonSerializable
{

    /** @var ConstraintViolationListInterface */
    protected $violations;

    /** @var FamilyInterface */
    protected $family;

    /** @var string */
    protected $reference;

    /**
     * InvalidImportException constructor.
     * @param FamilyInterface                  $family
     * @param string                           $reference
     * @param ConstraintViolationListInterface $violations
     * @param Throwable|null                   $previous
     */
    public function __construct($family, $reference, $violations, Throwable $previous = null)
    {
        $violationCount = count($violations);
        $message = "Invalid fixtures data for reference {$reference} of family '{$family->getCode()}' : {$violationCount} violations";
        parent::__construct($message, 0, $previous);

        $this->violations = $violations;
        $this->family = $family;
        $this->reference = $reference;
    }


    /**
     * @param FamilyInterface                  $family
     * @param string                           $reference
     * @param ConstraintViolationListInterface $violations
     * @param Throwable|null                   $previous
     *
     * @return InvalidImportException
     */
    public static function create($family, $reference, $violations, Throwable $previous = null)
    {
        return new InvalidImportException($family, $reference, $violations, $previous);
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        $data = [
            'message' => $this->message,
            'family' => $this->family->getCode(),
            'reference' => $this->reference,
            'violations' => [],
        ];

        /** @var ConstraintViolationInterface $violation */
        foreach ($this->violations as $violation) {
            $data['violations'][] = [
                'code' => $violation->getCode(),
                'message' => $violation->getMessage(),
                'property_path' => $violation->getPropertyPath(),
                'invalid_value' => $violation->getInvalidValue(),
            ];
        }

        return json_encode($data);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

}
