<?php

namespace CleverAge\EAVManager\AdminBundle\Model;

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\EAVRelationAttributeType;
use CleverAge\EAVManager\AdminBundle\Form\Type\EmbedMultiFamilyCollectionType;

/**
 * Force collection type and inject the attribute into the collection
 */
class EmbedMultiFamilyAttributeType extends EAVRelationAttributeType
{
    /**
     * @param AttributeInterface $attribute
     */
    public function setAttributeDefaults(AttributeInterface $attribute)
    {
        $attribute->addOption('attribute_config', [
            'collection_type' => EmbedMultiFamilyCollectionType::class,
        ]);
    }
}
