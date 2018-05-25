<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AssetBundle\Serializer\Normalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oneup\UploaderBundle\Uploader\Response\EmptyResponse;
use Sidus\EAVModelBundle\Serializer\ByReferenceHandler;
use Sidus\EAVModelBundle\Serializer\MaxDepthHandler;
use Sidus\FileUploadBundle\Controller\BlueimpController;
use Sidus\FileUploadBundle\Manager\ResourceManagerInterface;
use Sidus\FileUploadBundle\Model\ResourceInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;
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

    /** @var ResourceManagerInterface */
    protected $resourceManager;

    /** @var BlueimpController[] */
    protected $uploadManagers;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var MaxDepthHandler */
    protected $maxDepthHandler;

    /** @var ByReferenceHandler */
    protected $byReferenceHandler;

    /**
     * @param ClassMetadataFactoryInterface|null  $classMetadataFactory
     * @param NameConverterInterface|null         $nameConverter
     * @param PropertyAccessorInterface|null      $propertyAccessor
     * @param PropertyTypeExtractorInterface|null $propertyTypeExtractor
     * @param ResourceManagerInterface            $resourceManager
     * @param BlueimpController[]                 $uploadManagers
     * @param ManagerRegistry                     $doctrine
     * @param MaxDepthHandler                     $maxDepthHandler
     * @param ByReferenceHandler                  $byReferenceHandler
     *
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException
     */
    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ResourceManagerInterface $resourceManager,
        array $uploadManagers,
        ManagerRegistry $doctrine,
        MaxDepthHandler $maxDepthHandler,
        ByReferenceHandler $byReferenceHandler
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);
        $this->resourceManager = $resourceManager;
        $this->uploadManagers = $uploadManagers;
        $this->doctrine = $doctrine;
        $this->maxDepthHandler = $maxDepthHandler;
        $this->byReferenceHandler = $byReferenceHandler;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws RuntimeException
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
     * @param mixed  $data
     * @param string $class
     * @param string $format
     * @param array  $context
     *
     * @throws \Exception
     *
     * @return ResourceInterface
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $resolver = new OptionsResolver();
        $this->configureDenormalizeOptions($resolver);
        $options = $resolver->resolve(array_key_exists(self::OPTION_KEY, $context) ? $context[self::OPTION_KEY] : []);

        $entityManager = $this->doctrine->getManagerForClass($class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \UnexpectedValueException("No manager found for class {$class}");
        }
        $repository = $entityManager->getRepository($class);

        if (empty($data)) {
            return null;
        }

        if (is_scalar($data)) {
            // Test base identifier
            /** @var ResourceInterface $resource */
            $resource = $repository->find($data);
            if (null === $resource) {
                $resource = $repository->findOneBy(['path' => $data]);
            }
            if ($resource) {
                return $resource;
            }

            return $this->uploadFile($data, $class, $options);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */

        return parent::denormalize($data, $class, $format, $context);
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
     * @param string            $format
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
        $this->configureNormalizeOptions($resolver);
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
    protected function configureNormalizeOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'url' => true,
                'path' => false,
                'absolute_path' => false,
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    protected function configureDenormalizeOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'import_path' => null,
                'copy_file' => true,
                'ignore_missing' => true,
            ]
        );
    }

    /**
     * @param string $data
     * @param string $class
     * @param array  $options
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @throws \UnexpectedValueException
     * @throws RuntimeException
     *
     * @return ResourceInterface
     */
    protected function uploadFile($data, $class, array $options)
    {
        $importPath = $options['import_path'] ?? null;
        $filePath = rtrim($importPath, '/').'/'.$data;
        if (!file_exists($filePath)) {
            if ($options['ignore_missing']) {
                return null;
            }
            throw new RuntimeException("Unable to denormalize resource based on data '{$data}'");
        }

        $type = \call_user_func([$class, 'getType']);
        if (!isset($this->uploadManagers[$type])) {
            throw new RuntimeException("Unknown upload type {$type}");
        }
        $uploadManager = $this->uploadManagers[$type];
        if ($options['copy_file']) {
            // Copy file to tmp
            $tmpFilePath = sys_get_temp_dir().'/'.basename($filePath);
            if (!@copy($filePath, $tmpFilePath)) {
                throw new RuntimeException("Unable to copy file {$filePath} to temporary destination {$tmpFilePath}");
            }
        } else {
            $tmpFilePath = $filePath;
        }

        $file = new File($tmpFilePath);
        $response = new EmptyResponse();
        $file = $uploadManager->handleManualUpload($file, $response);
        if (!$file instanceof ResourceInterface) {
            $errorClass = \get_class($file);
            throw new RuntimeException("Unexpected response from file upload, got: {$errorClass}");
        }
        $file->setOriginalFileName(basename($filePath));

        return $file;
    }
}
