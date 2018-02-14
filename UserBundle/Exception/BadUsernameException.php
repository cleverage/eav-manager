<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Thrown at user creation when the username is not valid
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
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

        return new self(implode("\n", $messages));
    }
}
