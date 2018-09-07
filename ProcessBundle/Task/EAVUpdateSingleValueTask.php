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

use CleverAge\ProcessBundle\Model\ProcessState;
use CleverAge\ProcessBundle\Task\Doctrine\AbstractDoctrineTask;
use Doctrine\ORM\EntityManager;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Exception\MissingAttributeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Update an flush a single value from an EAV data
 */
class EAVUpdateSingleValueTask extends AbstractDoctrineTask
{
    /**
     * @param ProcessState $state
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     * @throws \InvalidArgumentException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Sidus\EAVModelBundle\Exception\InvalidValueDataException
     * @throws \Sidus\EAVModelBundle\Exception\ContextException
     * @throws \Doctrine\ORM\ORMException
     */
    public function execute(ProcessState $state)
    {
        $entity = $state->getInput();
        if (!$entity instanceof DataInterface) {
            $state->setException(new \UnexpectedValueException('Expecting a DataInterface as input'));
            $state->setError($entity);

            return;
        }
        $family = $entity->getFamily();
        $options = $this->getOptions($state);
        if (!$family->hasAttribute($options['attribute'])) {
            $state->setException(new MissingAttributeException($options['attribute']));
            $state->setError($entity);

            return;
        }

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $attribute = $family->getAttribute($options['attribute']);
        $entity->set($attribute->getCode(), $options['value']); // Set actual value

        $valueEntity = $entity->getValue($attribute); // Get value "entity" with updated value
        $em->persist($valueEntity); // Persist if new
        $em->flush($valueEntity); // Flush value ONLY
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(
            [
                'attribute',
                'value',
            ]
        );
        $resolver->setAllowedTypes('attribute', ['string']);
    }
}
