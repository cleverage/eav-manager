<?php

namespace CleverAge\EAVManager\UserBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Lancé lors de la création d'un utilisateur si le username n'est pas valide
 */
class BadUsernameException extends \RuntimeException
{
    /**
     * @param ConstraintViolationListInterface $constraintViolationList
     *
     * @return BadUsernameException
     */
    public static function createFromViolations(ConstraintViolationListInterface $constraintViolationList)
    {
        $messages = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($constraintViolationList as $violation) {
            $messages[] = $violation->getMessage();
        }

        return new BadUsernameException(implode("\n", $messages));
    }
}
