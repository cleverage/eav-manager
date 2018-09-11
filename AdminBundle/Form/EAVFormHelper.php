<?php

namespace CleverAge\EAVManager\AdminBundle\Form;

use Sidus\AdminBundle\Admin\Action;
use Sidus\BaseBundle\Translator\TranslatableTrait;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Clever Data Manager extension to base admin form helper
 *
 * {@inheritdoc}
 */
class EAVFormHelper
{
    use TranslatableTrait;

    /** @var FormHelper */
    protected $baseFormHelper;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /**
     * @param TranslatorInterface  $translator
     * @param FormHelper           $baseFormHelper
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(
        TranslatorInterface $translator,
        FormHelper $baseFormHelper,
        FormFactoryInterface $formFactory
    ) {
        $this->translator = $translator;
        $this->baseFormHelper = $baseFormHelper;
        $this->formFactory = $formFactory;
    }

    /**
     * @param Action  $action
     * @param Request $request
     * @param mixed   $data
     * @param array   $options
     *
     * @return FormInterface
     */
    public function getForm(
        Action $action,
        Request $request,
        DataInterface $data,
        array $options = []
    ): FormInterface {
        $defaultOptions = $this->getDefaultFormOptions($action, $request, $data);

        return $this->getFormBuilder($action, $data, array_merge($defaultOptions, $options))->getForm();
    }

    /**
     * @param Action        $action
     * @param Request       $request
     * @param DataInterface $data
     *
     * @return FormInterface
     */
    public function getEmptyForm(
        Action $action,
        Request $request,
        DataInterface $data
    ): FormInterface {
        $formOptions = $this->getDefaultFormOptions($action, $request, $data);
        unset($formOptions['family'], $formOptions['fieldset_options']);

        return $this->formFactory->createNamedBuilder(
            "form_{$action->getAdmin()->getCode()}_{$action->getCode()}",
            FormType::class,
            null,
            $formOptions
        )->getForm();
    }

    /**
     * @param Action $action
     * @param mixed  $data
     * @param array  $options
     *
     * @throws \UnexpectedValueException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     *
     * @return FormBuilderInterface
     */
    public function getFormBuilder(Action $action, $data, array $options = []): FormBuilderInterface
    {
        return $this->baseFormHelper->getFormBuilder($action, $data, $options);
    }

    /**
     * @param Action        $action
     * @param Request       $request
     * @param DataInterface $data
     *
     * @return array
     */
    public function getDefaultFormOptions(
        Action $action,
        Request $request,
        DataInterface $data
    ): array {
        $formOptions = $this->baseFormHelper->getDefaultFormOptions($action, $request, $data->getId());
        $formOptions['family'] = $data->getFamily();
        $formOptions['label'] = $this->tryTranslate(
            [
                "admin.family.{$data->getFamilyCode()}.{$action->getCode()}.title",
                "admin.{$action->getAdmin()->getCode()}.{$action->getCode()}.title",
                "admin.action.{$action->getCode()}.title",
            ],
            [],
            ucfirst($action->getCode())
        );

        return array_merge($formOptions, $data->getFamily()->getFormOptions());
    }
}
