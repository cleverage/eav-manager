<?php

namespace CleverAge\EAVManager\AdminBundle\Form;

use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Sidus\BaseBundle\Translator\TranslatableTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Clever Data Manager extension to base admin form helper
 *
 * {@inheritdoc}
 */
class FormHelper extends \Sidus\AdminBundle\Form\FormHelper
{
    use TranslatableTrait;

    /** @var string */
    protected $defaultTarget;

    /**
     * @param RoutingHelper        $routingHelper
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface  $translator
     * @param string               $defaultTarget
     */
    public function __construct(
        RoutingHelper $routingHelper,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        string $defaultTarget = '#tg_right'
    ) {
        parent::__construct($routingHelper, $formFactory);
        $this->translator = $translator;
        $this->defaultTarget = $defaultTarget;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultFormOptions(Action $action, Request $request, $dataId = null): array
    {
        $formOptions = parent::getDefaultFormOptions($action, $request, $dataId);
        $formOptions['show_legend'] = false;

        if ($request->isXmlHttpRequest()) { // Target should not be used when not calling through Ajax
            $target = $this->getTarget($request);
            if (null !== $target) {
                $formOptions['attr']['data-target-element'] = $target;
            }
        }
        $formOptions['label'] = $this->tryTranslate(
            [
                "admin.{$action->getAdmin()->getCode()}.{$action->getCode()}.title",
                "admin.action.{$action->getCode()}.title",
            ],
            [],
            ucfirst($action->getCode())
        );

        return $formOptions;
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    public function getTarget(Request $request): ?string
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->defaultTarget;
        }

        return $request->get('target', $this->defaultTarget);
    }
}
