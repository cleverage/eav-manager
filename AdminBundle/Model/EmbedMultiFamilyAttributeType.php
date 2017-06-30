<?php

namespace CleverAge\EAVManager\AdminBundle\Model;

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\RelationAttributeType;
use CleverAge\EAVManager\AdminBundle\Form\Type\EmbedMultiFamilyCollectionType;

/**
 * Force collection type and inject the attribute into the collection
 */
class EmbedMultiFamilyAttributeType extends RelationAttributeType
{
    /**
     * @param AttributeInterface $attribute
     */
    public function setAttributeDefaults(AttributeInterface $attribute)
    {
        $attribute->addOption('attribute_config', [
            'collection_type' => EmbedMultiFamilyCollectionType::class,
        ]);
        $formOptions = $attribute->getFormOptions();
        $attribute->addFormOption('attribute', $attribute);
        $collectionOptions = [];
        if (array_key_exists('collection_options', $attribute->getFormOptions())) {
            $collectionOptions = $formOptions['collection_options'];
        }
        $attribute->addFormOption('collection_options', array_merge($collectionOptions, [
            'attribute' => $attribute,
        ]));
    }
}
