<?php

namespace CleverAge\EAVManager\ImportBundle\Transformer;


use CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;

class FamilyRelationTransformer implements EAVValueTransformerInterface
{

    /** @var RegistryInterface */
    protected $doctrine;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var string */
    protected $foreignAttribute;

    /** @var AttributeInterface */
    protected $currentAttribute;

    /**
     * FamilyRelationTransformer constructor.
     * @param RegistryInterface $doctrine
     * @param FamilyRegistry    $familyRegistry
     */
    public function __construct(RegistryInterface $doctrine, FamilyRegistry $familyRegistry)
    {
        $this->doctrine = $doctrine;
        $this->familyRegistry = $familyRegistry;
    }

    /**
     * @param string $foreignAttribute
     */
    public function setForeignAttribute(string $foreignAttribute)
    {
        $this->foreignAttribute = $foreignAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(FamilyInterface $family, AttributeInterface $attribute, $value, array $config = null)
    {
        // TODO: Implement transform() method.
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform(
        FamilyInterface $family,
        AttributeInterface $attribute,
        $value,
        array $config = null
    ) {
        $this->currentAttribute = $attribute;

        return is_array($value) ? array_map([$this, 'resolveRelation'], $value) : $this->resolveRelation($value);
    }

    /**
     * @TODO ADD CACHE
     *
     * @param mixed $value
     *
     * @return DataInterface
     */
    public function resolveRelation($value)
    {
        if ($this->currentAttribute->getType()->isRelation()) {
            $families = $this->currentAttribute->getOption('allowed_families');

            if (count($families) != 1) {
                throw new NotImplementedException("Not yet implemented");
            }

            $targetFamily = $this->familyRegistry->getFamily($families[0]);

            /** @var DataRepository $repo */
            $repo = $this->doctrine->getRepository($targetFamily->getDataClass());
            $eavQB = $repo->createFamilyQueryBuilder($targetFamily);

            $targetAttribute = $this->foreignAttribute ?? $targetFamily->getAttributeAsIdentifier()->getCode();
            $qb = $eavQB->apply($eavQB->a($targetAttribute)->equals($value));

            $result = $qb->getQuery()->getResult();

            $resultCount = count($result);
            if ($resultCount !== 1) {
                throw new \UnexpectedValueException(
                    "Not exactly one result, but {$resultCount} while matching {$targetAttribute}={$value} in family {$targetFamily->getCode()}"
                );
            }

            return $result[0];
        }

        throw new \UnexpectedValueException("Attribute {$this->currentAttribute->getCode()} is not a relation");
    }

}
