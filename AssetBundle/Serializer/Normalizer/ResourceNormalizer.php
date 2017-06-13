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

namespace CleverAge\EAVManager\AssetBundle\Serializer\Normalizer;

use Sidus\EAVModelBundle\Serializer\ByReferenceHandler;
use Sidus\EAVModelBundle\Serializer\MaxDepthHandler;
use Sidus\FileUploadBundle\Manager\ResourceManager;
use Sidus\FileUploadBundle\Model\ResourceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Normalize assets directly with the link to the resource.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class ResourceNormalizer extends ObjectNormalizer
{
    const OPTION_KEY = 'resource_options';

    /** @var ResourceManager */
    protected $resourceManager;

    /** @var MaxDepthHandler */
    protected $maxDepthHandler;

    /** @var ByReferenceHandler */
    protected $byReferenceHandler;

    /**
     * @param ClassMetadataFactoryInterface|null  $classMetadataFactory
     * @param NameConverterInterface|null         $nameConverter
     * @param PropertyAccessorInterface|null      $propertyAccessor
     * @param PropertyTypeExtractorInterface|null $propertyTypeExtractor
     * @param ResourceManager                     $resourceManager
     * @param MaxDepthHandler                     $maxDepthHandler
     * @param ByReferenceHandler                  $byReferenceHandler
     */
    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ResourceManager $resourceManager,
        MaxDepthHandler $maxDepthHandler,
        ByReferenceHandler $byReferenceHandler
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);
        $this->resourceManager = $resourceManager;
        $this->maxDepthHandler = $maxDepthHandler;
        $this->byReferenceHandler = $byReferenceHandler;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $this->maxDepthHandler->handleMaxDepth($context);

        /** @var ResourceInterface $object */
        if ($this->byReferenceHandler->isByShortReference($context)) {
            return $object->getIdentifier();
        }

        if ($this->byReferenceHandler->isByReference($context)) {
            $normalizedData = [
                'identifier' => $object->getIdentifier(),
                'originalFileName' => $object->getOriginalFileName(),
                'type' => $object->getType(),
            ];
        } else {
            $normalizedData = parent::normalize($object, $format, $context);
        }

        return $this->handleCustomFields($object, $format, $context, $normalizedData);
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed  $data   Data to denormalize from
     * @param string $type   The class to which the data should be denormalized
     * @param string $format The format being deserialized from
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_a($type, ResourceInterface::class, true);
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed  $data   Data to normalize
     * @param string $format The format being (de-)serialized from or into
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ResourceInterface;
    }

    /**
     * @param ResourceInterface $resource
     * @param                   $format
     * @param array             $context
     * @param array             $normalizedData
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function handleCustomFields(ResourceInterface $resource, $format, array $context, array $normalizedData)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve(array_key_exists(self::OPTION_KEY, $context) ? $context[self::OPTION_KEY] : []);

        if ($options['url']) {
            $normalizedData['@url'] = $this->resourceManager->getFileUrl(
                $resource,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        if ($options['path']) {
            $file = $this->resourceManager->getFile($resource);
            $normalizedData['path'] = $file->getPath();
        }

        return $normalizedData;
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'url' => true,
                'path' => false,
                'absolute_path' => false,
            ]
        );
    }
}
