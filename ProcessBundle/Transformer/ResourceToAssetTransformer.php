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

use CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FileUploadBundle\Model\ResourceInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Find an asset based on a resource or create a new one
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class ResourceToAssetTransformer extends UniqueEAVFinderTransformer
{
    /** @var array */
    protected $familyMap = [
        'Image' => 'imageFile',
        'Document' => 'documentFile',
    ];

    /**
     * @param array $familyMap
     */
    public function setFamilyMap(array $familyMap): void
    {
        $this->familyMap = $familyMap;
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
        if (null === $value) {
            return null;
        }
        if (!$value instanceof ResourceInterface) {
            throw new \UnexpectedValueException('Expecting a ResourceInterface as input');
        }
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        return $this->findAsset($value, $options);
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'attribute' => null,
                'create_missing' => true,
            ]
        );
        $resolver->setAllowedTypes('attribute', ['NULL', 'string', AttributeInterface::class]);
        $resolver->setAllowedTypes('create_missing', ['bool']);
        $resolver->setNormalizer(
            'attribute',
            function (Options $options, $value) {
                /** @var FamilyInterface $family */
                $family = $options['family'];

                if (null !== $value) {
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

                if (!array_key_exists($family->getCode(), $this->familyMap)) {
                    throw new \UnexpectedValueException(
                        'Unknown asset family detected, please specify the attribute for resource storage'
                    );
                }

                return $this->familyMap[$family->getCode()];
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
        return 'resource_to_asset';
    }

    /**
     * @param ResourceInterface $resource
     * @param array             $options
     *
     * @throws \Exception
     *
     * @return \Sidus\EAVModelBundle\Entity\DataInterface
     */
    protected function findAsset(ResourceInterface $resource, array $options)
    {
        /** @var FamilyInterface $family */
        $family = $options['family'];
        /** @var AttributeInterface $attribute */
        $attribute = $options['attribute'];
        /** @var DataRepository $repository */
        $repository = $options['repository'];

        $eavQb = $repository->createFamilyQueryBuilder($family);
        $qb = $eavQb->apply($eavQb->attribute($attribute)->equals($resource));
        $results = $qb->getQuery()->getResult();
        $data = \count($results) ? reset($results) : null;

        if (null === $data) {
            if ($options['create_missing']) {
                $data = $family->createData();
                $data->set($attribute->getCode(), $resource);
            } elseif (!$options['ignore_missing']) {
                $msg = "Missing entity for family {$family->getCode()} and";
                $msg .= " attribute {$attribute->getCode()} with resource '{$resource->getIdentifier()}'";
                throw new \UnexpectedValueException($msg);
            }
        }

        return $data;
    }
}
