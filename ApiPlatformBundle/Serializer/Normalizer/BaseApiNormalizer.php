<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\ApiPlatformBundle\Serializer\Normalizer;

use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Serializer\ContextTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Removing any attributes from the Api Platform normalizer.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class BaseApiNormalizer extends AbstractItemNormalizer
{
    use ContextTrait;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param DenormalizerInterface $denormalizer
     */
    public function setDenormalizer(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        if ($this->normalizer instanceof SerializerAwareInterface) {
            $this->normalizer->setSerializer($serializer);
        }
        if ($this->denormalizer instanceof SerializerAwareInterface) {
            $this->denormalizer->setSerializer($serializer);
        }
        if ($this->normalizer instanceof NormalizerAwareInterface && $serializer instanceof NormalizerInterface) {
            $this->normalizer->setNormalizer($serializer);
        }
        if ($this->denormalizer instanceof DenormalizerAwareInterface && $serializer instanceof DenormalizerInterface) {
            $this->denormalizer->setDenormalizer($serializer);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ApiPlatform\Core\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\CircularReferenceException
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\LogicException
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $resourceClass = $this->resourceClassResolver->getResourceClass(
            $object,
            $context['resource_class'] ?? null,
            true
        );
        $context = $this->initContext($resourceClass, $context);
        $context['api_normalize'] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws \ApiPlatform\Core\Exception\PropertyNotFoundException
     * @throws \ApiPlatform\Core\Exception\ResourceClassNotFoundException
     * @throws \Symfony\Component\Serializer\Exception\BadMethodCallException
     * @throws \Symfony\Component\Serializer\Exception\ExtraAttributesException
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\LogicException
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException
     * @throws \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        // Avoid issues with proxies if we populated the object
        if (isset($data['id']) && !isset($context['object_to_populate'])) {
            if (isset($context['api_allow_update']) && true !== $context['api_allow_update']) {
                throw new InvalidArgumentException('Update is not allowed for this operation.');
            }

            $this->updateObjectToPopulate($data, $context);
        }

        $context['api_denormalize'] = true;
        if (!isset($context['resource_class'])) {
            $context['resource_class'] = $class;
        }

        return $this->denormalizer->denormalize($data, $class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return
            $this->normalizer
            && $this->normalizer->supportsNormalization($data, $format)
            && parent::supportsNormalization($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            $this->denormalizer
            && $this->denormalizer->supportsDenormalization($data, $type, $format)
            && parent::supportsDenormalization($data, $type, $format);
    }

    /**
     * @param array $data
     * @param array $context
     *
     * @throws \ApiPlatform\Core\Exception\InvalidArgumentException
     * @throws \ApiPlatform\Core\Exception\PropertyNotFoundException
     * @throws \ApiPlatform\Core\Exception\ResourceClassNotFoundException
     */
    protected function updateObjectToPopulate(array $data, array &$context)
    {
        try {
            $context['object_to_populate'] = $this->iriConverter->getItemFromIri(
                (string) $data['id'],
                $context + ['fetch_data' => false]
            );
        } catch (InvalidArgumentException $e) {
            $identifier = null;
            $properties = $this->propertyNameCollectionFactory->create($context['resource_class'], $context);
            foreach ($properties as $propertyName) {
                if ($this->propertyMetadataFactory->create($context['resource_class'], $propertyName)->isIdentifier()) {
                    $identifier = $propertyName;
                    break;
                }
            }

            if (null === $identifier) {
                throw $e;
            }

            $context['object_to_populate'] = $this->iriConverter->getItemFromIri(
                sprintf(
                    '%s/%s',
                    $this->iriConverter->getIriFromResourceClass($context['resource_class']),
                    $data[$identifier]
                ),
                $context + ['fetch_data' => false]
            );
        }
    }
}
