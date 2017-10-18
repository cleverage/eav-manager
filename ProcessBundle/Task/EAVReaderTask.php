<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Psr\Log\LogLevel;

/**
 * Allows to iterate over a paged resultset of EAV data
 */
class EAVReaderTask extends AbstractEAVQueryTask implements IterableTaskInterface
{
    /** @var IterableResult */
    protected $iterator;

    /** @var bool */
    protected $closed = false;

    /**
     * {@inheritDoc}
     *
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
        if ($this->closed) {
            $options = $this->getOptions($state);
            $state->log('Reader was closed previously', LogLevel::ERROR, $options['family'], $options);
            $state->setStopped(true);

            return;
        }

        if (null === $this->iterator) {
            $qb = $this->getQueryBuilder($state);
            $query = $qb->getQuery();

            $this->iterator = $query->iterate();
            $this->iterator->next(); // Move to first element
        }

        $result = $this->iterator->current();

        // Handle empty results
        if (false === $result) {
            $options = $this->getOptions($state);
            $state->log('Empty resultset for query', LogLevel::WARNING, $options['family'], $options);
            $state->setStopped(true);

            return;
        }

        $state->setOutput(reset($result));
    }

    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration
     *
     * @param ProcessState $state
     *
     * @return bool
     * @throws \LogicException
     */
    public function next(ProcessState $state)
    {
        if (!$this->iterator instanceof IterableResult) {
            throw new \LogicException('No iterator initialized');
        }
        $this->iterator->next();

        $valid = $this->iterator->valid();
        if (!$valid) {
            $this->closed = true;
        }

        return $valid;
    }
}
