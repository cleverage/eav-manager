<?php

namespace CleverAge\EAVManager\ApiPlatformBundle\Serializer\Normalizer;

use ApiPlatform\Core\Api\IriConverterInterface;
use Doctrine\Common\Util\ClassUtils;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Exception\EAVExceptionInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use CleverAge\EAVManager\AssetBundle\Serializer\Normalizer\EAVAssetNormalizer as BaseEAVDataNormalizer;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Overriding relation handling.
 */
class EAVDataNormalizer extends BaseEAVDataNormalizer
{
    /** @var IriConverterInterface */
    protected $iriConverter;

    /**
     * @param IriConverterInterface $iriConverter
     */
    public function setIriConverter(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param DataInterface $object  object to normalize
     * @param string        $format  format the normalization result will be encoded as
     * @param array         $context Context options for the normalizer
     *
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException
     * @throws \Symfony\Component\PropertyAccess\Exception\ExceptionInterface
     * @throws \Sidus\EAVModelBundle\Exception\EAVExceptionInterface
     * @throws \Sidus\EAVModelBundle\Exception\InvalidValueDataException
     * @throws \Symfony\Component\Serializer\Exception\CircularReferenceException
     * @throws \ApiPlatform\Core\Exception\RuntimeException
     * @throws \ApiPlatform\Core\Exception\InvalidArgumentException
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        // Do the same for 'by_reference' ?
        if (
        $this->iriConverter
        && array_key_exists(self::BY_SHORT_REFERENCE_KEY, $context) ? $context[self::BY_SHORT_REFERENCE_KEY] : false
        ) {
            return $this->iriConverter->getIriFromItem($object);
        }

        return parent::normalize($object, $format, $context);
    }

    /**
     * We must override this method because we cannot expect the normalizer to work normally with collection with
     * the API Platform framework.
     *
     * @param DataInterface $object
     * @param string        $attribute
     * @param string        $format
     * @param array         $context
     *
     * @throws \Symfony\Component\PropertyAccess\Exception\ExceptionInterface
     *
     * @return mixed
     */
    protected function getAttributeValue(
        DataInterface $object,
        $attribute,
        $format = null,
        array $context = []
    ) {
        $rawValue = $this->propertyAccessor->getValue($object, $attribute);
        if (!is_array($rawValue) && !$rawValue instanceof \Traversable) {
            $subContext = $this->getAttributeContext($object, $attribute, $rawValue, $context);

            return $this->normalizer->normalize($rawValue, $format, $subContext);
        }

        $collection = [];
        /** @var array $rawValue */
        foreach ($rawValue as $item) {
            $subContext = $this->getAttributeContext($object, $attribute, $item, $context);
            $collection[] = $this->normalizer->normalize($item, $format, $subContext);
        }

        return $collection;
    }

    /**
     * We must override this method because we cannot expect the normalizer to work normally with collection with
     * the API Platform framework.
     *
     * @param DataInterface      $object
     * @param AttributeInterface $attribute
     * @param string             $format
     * @param array              $context
     *
     * @throws EAVExceptionInterface
     *
     * @return mixed
     */
    protected function getEAVAttributeValue(
        DataInterface $object,
        AttributeInterface $attribute,
        $format = null,
        array $context = []
    ) {
        $rawValue = $object->get($attribute->getCode());
        if (!is_array($rawValue) && !$rawValue instanceof \Traversable) {
            $subContext = $this->getEAVAttributeContext($object, $attribute, $rawValue, $context);

            return $this->normalizer->normalize($rawValue, $format, $subContext);
        }

        $collection = [];
        /** @var array $rawValue */
        foreach ($rawValue as $item) {
            $subContext = $this->getEAVAttributeContext($object, $attribute, $item, $context);
            $collection[] = $this->normalizer->normalize($item, $format, $subContext);
        }

        return $collection;
    }

    /**
     * @param DataInterface $object
     * @param string        $attribute
     * @param mixed         $rawValue
     * @param array         $context
     *
     * @return array
     */
    protected function getAttributeContext(
        DataInterface $object,
        $attribute,
        /* @noinspection PhpUnusedParameterInspection */
        $rawValue,
        array $context
    ) {
        $resolvedContext = parent::getAttributeContext($object, $attribute, $rawValue, $context);

        if (!is_object($rawValue)) {
            return $resolvedContext;
        }

        $resolvedContext['resource_class'] = ClassUtils::getClass($rawValue);
        unset($resolvedContext['item_operation_name'], $resolvedContext['collection_operation_name']);

        return $resolvedContext;
    }

    /**
     * @param DataInterface      $object
     * @param AttributeInterface $attribute
     * @param mixed              $rawValue
     * @param array              $context
     *
     * @return array
     */
    protected function getEAVAttributeContext(
        DataInterface $object,
        AttributeInterface $attribute,
        /* @noinspection PhpUnusedParameterInspection */
        $rawValue,
        array $context
    ) {
        $resolvedContext = parent::getEAVAttributeContext($object, $attribute, $rawValue, $context);

        if (!is_object($rawValue)) {
            return $resolvedContext;
        }

        $resolvedContext['resource_class'] = ClassUtils::getClass($rawValue);
        unset($resolvedContext['item_operation_name'], $resolvedContext['collection_operation_name']);

        return $resolvedContext;
    }
}
