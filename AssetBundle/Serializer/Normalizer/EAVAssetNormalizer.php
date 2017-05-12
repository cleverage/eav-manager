<?php

namespace CleverAge\EAVManager\AssetBundle\Serializer\Normalizer;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Serializer\Normalizer\EAVDataNormalizer;

/**
 * Normalize assets directly with the link to the resource
 */
class EAVAssetNormalizer extends EAVDataNormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function isAllowedEAVAttribute(
        DataInterface $object,
        AttributeInterface $attribute,
        /** @noinspection PhpUnusedParameterInspection */
        $format = null,
        array $context = []
    ) {
        if (in_array($object->getFamilyCode(), ['Document', 'Image'], true)
            && in_array($attribute->getCode(), ['imageFile', 'documentFile'], true)
        ) {
            return true;
        }

        return parent::isAllowedEAVAttribute(
            $object,
            $attribute,
            $format,
            $context
        );
    }

}
