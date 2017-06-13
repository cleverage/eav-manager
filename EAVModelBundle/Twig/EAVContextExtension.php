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

namespace CleverAge\EAVManager\EAVModelBundle\Twig;

use Sidus\EAVModelBundle\Context\ContextManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Twig_Extension;

/**
 * Display context form in twig templates.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class EAVContextExtension extends Twig_Extension
{
    /** @var ContextManager */
    protected $contextManager;

    /**
     * @param ContextManager $contextManager
     */
    public function __construct(ContextManager $contextManager)
    {
        $this->contextManager = $contextManager;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('context_form', [$this, 'getContextForm'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return null|FormView
     *
     * @throws InvalidOptionsException
     */
    public function getContextForm()
    {
        $form = $this->contextManager->getContextSelectorForm();
        if (!$form) {
            return null;
        }

        return $form->createView();
    }

    /**
     * @return string The extension name
     */
    public function getName()
    {
        return 'eavmanager_context';
    }
}
