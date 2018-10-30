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

use CleverAge\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sidus\EAVModelBundle\Doctrine\EAVFinder;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Allows to iterate over a paged resultset of EAV data
 */
class EAVReaderTask extends AbstractEAVQueryTask implements IterableTaskInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var \Iterator */
    protected $iterator;

    /** @var bool */
    protected $closed = false;

    /**
     * @param LoggerInterface        $logger
     * @param EntityManagerInterface $entityManager
     * @param FamilyRegistry         $familyRegistry
     * @param EAVFinder              $eavFinder
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        FamilyRegistry $familyRegistry,
        EAVFinder $eavFinder
    ) {
        $this->logger = $logger;
        parent::__construct($entityManager, $familyRegistry, $eavFinder);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     * @throws \Pagerfanta\Exception\NotIntegerCurrentPageException
     * @throws \Pagerfanta\Exception\OutOfRangeCurrentPageException
     * @throws \Pagerfanta\Exception\LessThan1CurrentPageException
     * @throws \UnexpectedValueException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function execute(ProcessState $state)
    {
        $options = $this->getOptions($state);
        if ($this->closed) {
            $logContext = $this->getLogContext($state);
            if ($options['allow_reset']) {
                $this->closed = false;
                $this->iterator = null;
                $this->logger->error('Reader was closed previously, restarting it', $logContext);
            } else {
                $this->logger->error('Reader was closed previously, stopping the process', $logContext);
                $state->setStopped(true);

                return;
            }
        }

        if (null === $this->iterator) {
            $paginator = $this->getPaginator($state);
            $this->iterator = $paginator->getIterator();

            // Log the data count
            if ($this->getOption($state, 'log_count')) {
                $count = \count($paginator);
                $logContext = $this->getLogContext($state);
                $this->logger->info("{$count} items found with current query", $logContext);
            }
        }

        // Handle empty results
        if (0 === $this->iterator->count()) {
            if ($this->getOption($state, 'log_count')) {
                $logContext = $this->getLogContext($state);
                $this->logger->notice('Empty resultset for query, stopping the process', $logContext);
            }
            $state->setStopped(true);

            return;
        }

        $state->setOutput($this->iterator->current());
    }

    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration
     *
     * @param ProcessState $state
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function next(ProcessState $state)
    {
        if (!$this->iterator instanceof \Iterator) {
            throw new \LogicException('No iterator initialized');
        }
        $this->iterator->next();

        $valid = $this->iterator->valid();
        if (!$valid) {
            $this->closed = true;
        }

        return $valid;
    }

    /**
     * {@inheritDoc}
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'allow_reset' => false,   // Allow the reader to reset it's iterator
                'log_count' => false,   // Log in state history the result count
            ]
        );
    }

    /**
     * @param \CleverAge\ProcessBundle\Model\ProcessState $state
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     *
     * @return array
     */
    protected function getLogContext(ProcessState $state)
    {
        $logContext = [];
        $options = $this->getOptions($state);
        if (array_key_exists('family', $options)) {
            $options['family'] = $options['family']->getCode();
        }
        if (array_key_exists('repository', $options)) {
            $options['repository'] = \get_class($options['repository']);
        }
        $logContext['options'] = $options;

        return $logContext;
    }
}
