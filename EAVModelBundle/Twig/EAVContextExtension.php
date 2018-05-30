<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\EAVModelBundle\Twig;

use Sidus\EAVModelBundle\Context\ContextManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Twig_Extension;

/**
 * @deprecated ContextManagerInterface does not declare the getContextForm method (anymore)
 *
 * @todo refactor with a different service
 *
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
