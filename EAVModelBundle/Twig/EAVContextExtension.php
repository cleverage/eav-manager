<?php

namespace CleverAge\EAVManager\EAVModelBundle\Twig;

use Sidus\EAVModelBundle\Context\ContextManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Twig_Extension;

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
