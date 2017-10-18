<?php

namespace CleverAge\EAVManager\ProcessBundle\Task;

use CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Handles EAV Data pagination
 */
abstract class AbstractEAVQueryTask extends AbstractEAVTask
{
    /**
     * {@inheritDoc}
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'criteria' => [],
                'repository' => null,
                'order_by' => [],
                'limit' => null,
                'offset' => null,
            ]
        );
        $resolver->setNormalizer(
            'repository',
            function (Options $options, $value) {
                if ($value instanceof DataRepository) {
                    return $value;
                }
                /** @var FamilyInterface $family */
                $family = $options['family'];

                return $this->doctrine->getRepository($family->getDataClass());
            }
        );

        $resolver->setAllowedTypes('criteria', ['array']);
        $resolver->setAllowedTypes('repository', ['NULL', DataRepository::class]);
        $resolver->setAllowedTypes('order_by', ['array']);
        $resolver->setAllowedTypes('limit', ['NULL', 'integer']);
        $resolver->setAllowedTypes('offset', ['NULL', 'integer']);
    }

    /**
     * @param ProcessState $state
     * @param string       $alias
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder(ProcessState $state, $alias = 'e')
    {
        $options = $this->getOptions($state);
        /** @var DataRepository $repository */
        $repository = $options['repository'];
        /** @var FamilyInterface $family */
        $family = $options['family'];
        $eavQb = $repository->createFamilyQueryBuilder($family, $alias);

        $queryParts = [];
        /** @noinspection ForeachSourceInspection */
        foreach ($options['criteria'] as $attributeCode => $value) {
            if (is_array($value)) {
                $queryParts[] = $eavQb->a($attributeCode)->in($value);
            } else {
                if (null !== $value && $value === $family->getAttribute($attributeCode)->getDefault()) {
                    $queryParts[] = $eavQb->getOr(
                        [
                            $eavQb->a($attributeCode)->equals($value),
                            $eavQb->a($attributeCode)->isNull(), // Handles default values not persisted to database
                        ]
                    );
                } else {
                    $queryParts[] = $eavQb->a($attributeCode)->equals($value);
                }
            }
        }
        /** @noinspection ForeachSourceInspection */
        foreach ($options['order_by'] as $attributeCode => $order) {
            $eavQb->addOrderBy($eavQb->a($attributeCode), $order);
        }

        $qb = $eavQb->apply($eavQb->getAnd($queryParts));
        if (null !== $options['limit']) {
            $qb->setMaxResults($options['limit']);
        }
        if (null !== $options['offset']) {
            $qb->setFirstResult($options['offset']);
        }

        return $qb;
    }
}
