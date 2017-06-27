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
        if ($value === null) {
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

                if ($family->getCode() === 'Image') {
                    return 'imageFile';
                }
                if ($family->getCode() === 'Document') {
                    return 'documentFile';
                }

                throw new \UnexpectedValueException(
                    'Unknown asset family detected, please specify the attribute for resource storage'
                );
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
        $qb->setMaxResults(1);
        $data = $qb->getQuery()->getOneOrNullResult();

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
