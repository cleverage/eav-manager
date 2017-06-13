<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
