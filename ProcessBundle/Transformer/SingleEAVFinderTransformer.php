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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Find an EAV entity based on a unique attribute that is not an identifier
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class SingleEAVFinderTransformer implements ConfigurableTransformerInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * @param EntityManagerInterface $entityManager
     * @param FamilyRegistry         $familyRegistry
     */
    public function __construct(EntityManagerInterface $entityManager, FamilyRegistry $familyRegistry)
    {
        $this->entityManager = $entityManager;
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
            ]
        );
        $resolver->setAllowedTypes('family', ['string', FamilyInterface::class]);
        $resolver->setDefaults(
            [
                'repository' => null,
                'ignore_missing' => true,
            ]
        );
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
            'repository',
            function (Options $options, $value) {
                if ($value instanceof DataRepository) {
                    return $value;
                }
                /** @var FamilyInterface $family */
                $family = $options['family'];

                return $this->entityManager->getRepository($family->getDataClass());
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
        return 'single_eav_finder';
    }

    /**
     * @param array $value
     * @param array $options
     *
     * @throws \Exception
     *
     * @return \Sidus\EAVModelBundle\Entity\DataInterface
     */
    protected function findData($value, array $options)
    {
        if (!\is_array($value)) {
            $msg = 'Value must be an array';
            throw new \UnexpectedValueException($msg);
        }

        /** @var FamilyInterface $family */
        $family = $options['family'];
        /** @var DataRepository $repository */
        $repository = $options['repository'];

        $eavQb = $repository->createFamilyQueryBuilder($family, 'e');

        $queryParts = [];
        /** @noinspection ForeachSourceInspection */
        foreach ($value as $attributeCode => $attributeValue) {
            if (\is_array($attributeValue)) {
                $queryParts[] = $eavQb->a($attributeCode)->in($attributeValue);
            } else {
                if (null !== $attributeValue
                    && $attributeValue === $family->getAttribute($attributeCode)->getDefault()
                ) {
                    $queryParts[] = $eavQb->getOr(
                        [
                            $eavQb->a($attributeCode)->equals($attributeValue),
                            $eavQb->a($attributeCode)->isNull(), // Handles default values not persisted to database
                        ]
                    );
                } else {
                    $queryParts[] = $eavQb->a($attributeCode)->equals($attributeValue);
                }
            }
        }

        $qb = $eavQb->apply($eavQb->getAnd($queryParts));
        $qb->distinct();

        try {
            $data = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $msg = "Non unique entity for family {$family->getCode()} and";
            foreach ($value as $attribute => $attributeValue) {
                $msg .= " attribute {$attribute} with value '{$attributeValue}'";
            }
            throw new \UnexpectedValueException($msg);
        }

        if (null === $data && !$options['ignore_missing']) {
            $msg = "Missing entity for family {$family->getCode()} and";
            foreach ($value as $attribute => $attributeValue) {
                $msg .= " attribute {$attribute} with value '{$attributeValue}'";
            }
            throw new \UnexpectedValueException($msg);
        }

        return $data;
    }
}
