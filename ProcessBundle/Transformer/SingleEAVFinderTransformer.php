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

namespace CleverAge\EAVManager\ProcessBundle\Transformer;

use CleverAge\ProcessBundle\Transformer\ConfigurableTransformerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
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
    /** @var Registry */
    protected $doctrine;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * @param Registry       $doctrine
     * @param FamilyRegistry $familyRegistry
     */
    public function __construct(Registry $doctrine, FamilyRegistry $familyRegistry)
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
            ]
        );
        $resolver->setAllowedTypes('family', ['string', FamilyInterface::class]);
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
        if (!is_array($value)) {
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
            if (is_array($attributeValue)) {
                $queryParts[] = $eavQb->a($attributeCode)->in($attributeValue);
            } else {
                if (null !== $attributeValue && $attributeValue === $family->getAttribute($attributeCode)->getDefault()) {
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
