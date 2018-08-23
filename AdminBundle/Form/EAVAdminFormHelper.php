<?php

namespace CleverAge\EAVManager\AdminBundle\Form;

use Sidus\AdminBundle\Admin\Action;
use Sidus\BaseBundle\Translator\TranslatableTrait;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Clever Data Manager extension to base admin form helper
 *
 * {@inheritdoc}
 */
class EAVAdminFormHelper
{
    use TranslatableTrait;

    /** @var FormHelper */
    protected $baseFormHelper;

    /**
     * @param TranslatorInterface $translator
     * @param FormHelper          $baseFormHelper
     */
    public function __construct(TranslatorInterface $translator, FormHelper $baseFormHelper)
    {
        $this->translator = $translator;
        $this->baseFormHelper = $baseFormHelper;
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
        $defaultOptions = $this->getDefaultFormOptions($action, $request, $data->getFamily(), $data->getId());

        return $this->getFormBuilder($action, $data, array_merge($defaultOptions, $options))->getForm();
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
     * @param Action          $action
     * @param Request         $request
     * @param FamilyInterface $family
     * @param string          $dataId
     *
     * @return array
     */
    public function getDefaultFormOptions(
        Action $action,
        Request $request,
        FamilyInterface $family,
        $dataId = null
    ): array {
        $formOptions = $this->baseFormHelper->getDefaultFormOptions($action, $request, $dataId);
        $formOptions['family'] = $family;
        $formOptions['label'] = $this->tryTranslate(
            [
                "admin.family.{$family->getCode()}.{$action->getCode()}.title",
                "admin.{$action->getAdmin()->getCode()}.{$action->getCode()}.title",
                "admin.action.{$action->getCode()}.title",
            ],
            [],
            ucfirst($action->getCode())
        );

        return $formOptions;
    }
}
