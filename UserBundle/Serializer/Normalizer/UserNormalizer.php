<?php

namespace CleverAge\EAVManager\UserBundle\Serializer\Normalizer;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Sidus\EAVModelBundle\Serializer\Normalizer\EAVDataNormalizer;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Custom User normalizer, removing sensitive informations.
 */
class UserNormalizer extends ObjectNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var User $object */
        $byShortReference = false;
        if (array_key_exists(EAVDataNormalizer::BY_SHORT_REFERENCE_KEY, $context)) {
            $byShortReference = $context[EAVDataNormalizer::BY_SHORT_REFERENCE_KEY];
        }
        if ($byShortReference) {
            return $object->getId();
        }

        $byReference = false;
        if (array_key_exists(EAVDataNormalizer::BY_REFERENCE_KEY, $context)) {
            $byReference = $context[EAVDataNormalizer::BY_REFERENCE_KEY];
        }
        if ($byReference) {
            return [
                'id' => $object->getId(),
                'username' => $object->getUsername(),
            ];
        }

        try {
            return parent::normalize($object, $format, $context);
        } catch (CircularReferenceException $e) {
            return $object->getId();
        }
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
        return is_a($type, User::class, true);
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
        return $data instanceof User;
    }
}
