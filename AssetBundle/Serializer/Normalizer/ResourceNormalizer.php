<?php

namespace CleverAge\EAVManager\AssetBundle\Serializer\Normalizer;

use Sidus\EAVModelBundle\Serializer\Normalizer\EAVDataNormalizer;
use Sidus\FileUploadBundle\Manager\ResourceManager;
use Sidus\FileUploadBundle\Model\ResourceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Normalize assets directly with the link to the resource
 */
class ResourceNormalizer extends ObjectNormalizer
{
    /** @var ResourceManager */
    protected $resourceManager;

    /**
     * @param ResourceManager $resourceManager
     */
    public function setResourceManager($resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var ResourceInterface $object */
        $byShortReference = false;
        if (array_key_exists(EAVDataNormalizer::BY_SHORT_REFERENCE_KEY, $context)) {
            $byShortReference = $context[EAVDataNormalizer::BY_SHORT_REFERENCE_KEY];
        }
        if ($byShortReference) {
            return $object->getIdentifier();
        }

        $byReference = false;
        if (array_key_exists(EAVDataNormalizer::BY_REFERENCE_KEY, $context)) {
            $byReference = $context[EAVDataNormalizer::BY_REFERENCE_KEY];
        }
        if ($byReference) {
            return [
                'id' => $object->getIdentifier(),
                'originalFileName' => $object->getOriginalFileName(),
                'type' => $object->getType(),
                '@url' => $this->resourceManager->getFileUrl($object, UrlGeneratorInterface::ABSOLUTE_URL),
            ];
        }

        return parent::normalize($object, $format, $context);
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
}
