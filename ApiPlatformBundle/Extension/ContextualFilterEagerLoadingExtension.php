<?php


namespace CleverAge\EAVManager\ApiPlatformBundle\Extension;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\FilterEagerLoadingExtension;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use function is_a;
use Sidus\EAVModelBundle\Entity\DataInterface;

/**
 * Overrides base FilterEagerLoadingExtension because it breaks the DQL for no reason
 */
class ContextualFilterEagerLoadingExtension implements ContextAwareQueryCollectionExtensionInterface
{
    /** @var FilterEagerLoadingExtension */
    protected $baseExtension;

    /**
     * @param FilterEagerLoadingExtension $baseExtension
     */
    public function __construct(FilterEagerLoadingExtension $baseExtension)
    {
        $this->baseExtension = $baseExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if (is_a($resourceClass, DataInterface::class, true)) {
            return;
        }

        $this->baseExtension->applyToCollection(
            $queryBuilder,
            $queryNameGenerator,
            $resourceClass,
            $operationName,
            $context
        );
    }
}
