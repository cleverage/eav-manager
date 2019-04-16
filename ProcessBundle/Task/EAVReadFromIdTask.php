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

use CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sidus\EAVModelBundle\Doctrine\DataLoaderInterface;
use Sidus\EAVModelBundle\Entity\ContextualDataInterface;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Read EAV data from ID input
 */
class EAVReadFromIdTask extends AbstractEAVTask
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var DataLoaderInterface */
    protected $dataLoader;

    /**
     * @param EntityManagerInterface $entityManager
     * @param FamilyRegistry         $familyRegistry
     * @param DataLoaderInterface    $dataLoader
     * @param LoggerInterface        $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FamilyRegistry $familyRegistry,
        DataLoaderInterface $dataLoader,
        LoggerInterface $logger
    ) {
        parent::__construct($entityManager, $familyRegistry);
        $this->dataLoader = $dataLoader;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ExceptionInterface
     * @throws \UnexpectedValueException
     */
    public function execute(ProcessState $state)
    {
        $input = $state->getInput();
        $options = $this->getOptions($state);
        if (\is_array($input)) {
            if (!array_key_exists('id', $input)) {
                throw new \UnexpectedValueException('Expecting an array with the "id" key');
            }
            $input = $input['id'];
        }

        /** @var FamilyInterface $family */
        $family = $options['family'];
        /** @var DataRepository $repository */
        $repository = $this->entityManager->getRepository($family->getDataClass());
        $data = $repository->find($input);

        if (!$data instanceof DataInterface) {
            throw new \UnexpectedValueException("Data not found for id '{$input}'");
        }
        if ($data instanceof ContextualDataInterface && null !== $options['context']) {
            $data->setCurrentContext($options['context']);
        }
        $this->dataLoader->loadSingle($data, $options['load_depth']);

        $state->setOutput($data);
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'context' => null,
                'load_depth' => 1,
            ]
        );
        $resolver->setAllowedTypes('context', ['NULL', 'array']);
        $resolver->setAllowedTypes('load_depth', ['integer']);
    }
}
