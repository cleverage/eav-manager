<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\EAVManager\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManager;
use CleverAge\EAVManager\ProcessBundle\Model\AbstractConfigurableTask;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LogLevel;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\Internal\Hydration\IterableResult;

/**
 * Fetch entities from doctrine
 */
class DoctrineReaderTask extends AbstractConfigurableTask implements IterableTaskInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var IterableResult */
    protected $iterator;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration
     *
     * @param ProcessState $processState
     *
     * @return bool
     * @throws \LogicException
     */
    public function next(ProcessState $processState)
    {
        if (!$this->iterator instanceof IterableResult) {
            throw new \LogicException('No iterator initialized');
        }
        $this->iterator->next();

        return $this->iterator->valid();
    }

    /**
     * @param ProcessState $processState
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function execute(ProcessState $processState)
    {
        $options = $this->getOptions($processState);
        if (!$this->iterator) {
            $repository = $this->entityManager->getRepository($options['class_name']);
            $this->initIterator($repository, $options);
        }

        $result = $this->iterator->current();

        // Handle empty results
        if (false === $result) {
            $processState->log('Empty resultset for query', LogLevel::WARNING, $options['class_name'], $options);
            $processState->setStopped(true);

            return;
        }

        $processState->setOutput($result[0]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'class_name',
        ]);
        $resolver->setAllowedTypes('class_name', ['string']);
        $resolver->setDefaults([
            'criteria' => [],
            'order_by' => [],
            'limit' => null,
            'offset' => null,
        ]);
        $resolver->setAllowedTypes('criteria', ['array']);
        $resolver->setAllowedTypes('order_by', ['array']);
        $resolver->setAllowedTypes('limit', ['NULL', 'integer']);
        $resolver->setAllowedTypes('offset', ['NULL', 'integer']);
    }

    /**
     * @param EntityRepository $repository
     * @param array            $options
     */
    protected function initIterator(EntityRepository $repository, array $options)
    {
        $qb = $repository->createQueryBuilder('e');
        /** @noinspection ForeachSourceInspection */
        foreach ($options['criteria'] as $field => $value) {
            $parameterName = uniqid('param', false);
            if (null === $value) {
                $qb->andWhere("e.{$field} IS NULL");
            } else {
                if (is_array($value)) {
                    $qb->andWhere("e.{$field} IN (:{$parameterName})");
                } else {
                    $qb->andWhere("e.{$field} = :{$parameterName}");
                }
                $qb->setParameter($parameterName, $value);
            }
        }
        /** @noinspection ForeachSourceInspection */
        foreach ($options['order_by'] as $field => $order) {
            $qb->addOrderBy("e.{$field}", $order);
        }
        if (null !== $options['limit']) {
            $qb->setMaxResults($options['limit']);
        }
        if (null !== $options['offset']) {
            $qb->setFirstResult($options['offset']);
        }

        $this->iterator = $qb->getQuery()->iterate();
        $this->iterator->next(); // Move to first element
    }
}
