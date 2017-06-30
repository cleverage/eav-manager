<?php

namespace CleverAge\EAVManager\AdminBundle\Form\Type;

use Sidus\EAVBootstrapBundle\Form\Type\BootstrapCollectionType;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Very similar to the behavior of an embed type but allowing multi-families
 */
class EmbedMultiFamilyCollectionType extends AbstractType
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * @param FamilyRegistry $familyRegistry
     */
    public function __construct(FamilyRegistry $familyRegistry)
    {
        $this->familyRegistry = $familyRegistry;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allowed_families'] = $options['allowed_families'];
        $view->vars['allow_add'] = true;
        $view->vars['allow_delete'] = false;
        $view->vars['admin'] = $options['admin'];
        $view->vars['action'] = $options['action'];
        $view->vars['target'] = $options['target'] ?: "tg_{$view->vars['id']}_modal";
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'attribute',
        ]);
        $resolver->setAllowedTypes('attribute', [AttributeInterface::class]);
        $resolver->setDefaults([
            'allowed_families' => null,
            'admin' => '_data',
            'action' => 'create',
            'target' => null,
        ]);
        $resolver->setAllowedTypes('allowed_families', ['array', 'NULL']);
        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer(
            'allowed_families',
            function (Options $options, $values) {
                /** @var AttributeInterface $attribute */
                $attribute = $options['attribute'];
                $values = $attribute->getOption('allowed_families');
                if (null === $values) {
                    $values = $this->familyRegistry->getFamilies();
                }

                $families = [];
                /** @var array $values */
                foreach ($values as $value) {
                    if (!$value instanceof FamilyInterface) {
                        $value = $this->familyRegistry->getFamily($value);
                    }
                    if ($value->isInstantiable()) {
                        $families[$value->getCode()] = $value;
                    }
                }

                return $families;
            }
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'embed_multi_family_collection';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return BootstrapCollectionType::class;
    }
}
