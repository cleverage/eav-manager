<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\ProcessBundle\Task\AbstractDoctrineTask;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Handles EAV Data
 */
abstract class AbstractEAVTask extends AbstractDoctrineTask
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * {@inheritDoc}
     */
    public function __construct(Registry $doctrine, FamilyRegistry $familyRegistry)
    {
        parent::__construct($doctrine);
        $this->familyRegistry = $familyRegistry;
    }

    /**
     * {@inheritDoc}
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(
            [
                'family',
            ]
        );
        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setNormalizer(
            'family',
            function (Options $options, $value) {
                if ($value instanceof FamilyInterface) {
                    return $value;
                }

                return $this->familyRegistry->getFamily($value);
            }
        );
        $resolver->setAllowedTypes('family', ['string', FamilyInterface::class]);
    }
}
