<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\ProcessBundle\Transformer;

use CleverAge\ProcessBundle\Transformer\ConfigurableTransformerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Find an EAV entity based on a unique attribute that is not an identifier
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UniqueEAVFinderTransformer implements ConfigurableTransformerInterface
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * @param ManagerRegistry $doctrine
     * @param FamilyRegistry  $familyRegistry
     */
    public function __construct(ManagerRegistry $doctrine, FamilyRegistry $familyRegistry)
    {
        $this->doctrine = $doctrine;
        $this->familyRegistry = $familyRegistry;
    }

    /**
     * Must return the transformed $value
     *
     * @param mixed $value
     * @param array $options
     *
     * @throws \Exception
     *
     * @return mixed $value
     */
    public function transform($value, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        return $this->findData($value, $options);
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'family',
                'attribute',
            ]
        );
        $resolver->setAllowedTypes('family', ['string', FamilyInterface::class]);
        $resolver->setAllowedTypes('attribute', ['string', AttributeInterface::class]);
        $resolver->setDefaults(
            [
                'entity_manager' => null,
                'repository' => null,
                'ignore_missing' => true,
            ]
        );
        $resolver->setAllowedTypes('entity_manager', ['NULL', 'string', EntityManagerInterface::class]);
        $resolver->setAllowedTypes('repository', ['NULL', DataRepository::class]);
        $resolver->setAllowedTypes('ignore_missing', ['bool']);

        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer(
            'family',
            function (Options $options, $value) {
                if ($value instanceof FamilyInterface) {
                    return $value;
                }

                return $this->familyRegistry->getFamily($value);
            }
        );
        $resolver->setNormalizer(
            'attribute',
            function (Options $options, $value) {
                /** @var FamilyInterface $family */
                $family = $options['family'];
                if ($value instanceof AttributeInterface) {
                    if (!$family->hasAttribute($value->getCode())) {
                        throw new \UnexpectedValueException(
                            "Family {$family->getCode()} has no attribute named {$value->getCode()}"
                        );
                    }

                    return $value;
                }

                return $family->getAttribute($value);
            }
        );
        $resolver->setNormalizer(
            'repository',
            function (Options $options, $value) {
                if ($value instanceof DataRepository) {
                    return $value;
                }
                /** @var FamilyInterface $family */
                $family = $options['family'];

                return $this->doctrine->getManager($options['entity_manager'])->getRepository($family->getDataClass());
            }
        );
    }

    /**
     * Returns the unique code to identify the transformer
     *
     * @return string
     */
    public function getCode()
    {
        return 'unique_eav_finder';
    }

    /**
     * @param string|int $value
     * @param array      $options
     *
     * @throws \Exception
     *
     * @return \Sidus\EAVModelBundle\Entity\DataInterface
     */
    protected function findData($value, array $options)
    {
        /** @var FamilyInterface $family */
        $family = $options['family'];
        /** @var AttributeInterface $attribute */
        $attribute = $options['attribute'];
        /** @var DataRepository $repository */
        $repository = $options['repository'];

        $data = $repository->findByUniqueAttribute($family, $attribute, $value);
        if (null === $data && !$options['ignore_missing']) {
            $msg = "Missing entity for family {$family->getCode()} and";
            $msg .= " attribute {$attribute->getCode()} with value '{$value}'";
            throw new \UnexpectedValueException($msg);
        }

        return $data;
    }
}
