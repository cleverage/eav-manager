<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Model;

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\EAVEmbedAttributeType;
use CleverAge\EAVManager\AdminBundle\Form\Type\EmbedMultiFamilyCollectionType;

/**
 * Force collection type and inject the attribute into the collection
 */
class EmbedMultiFamilyAttributeType extends EAVEmbedAttributeType
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
