## Custom EAV Query

The right way.

### Problematics

#### Long queries

A query with very large joins will take a lot of time, the first thing to do is to strip any join that is not
used for data filtering or sorting. **Do not use join for select** use the OptimizedDataLoader instead.

#### Long COUNT() queries

When using the default Doctrine Paginator class (also used by PagerFanta), the count query is sub-optimal for the vast
majority of cases, that's why we developed a custom Paginator for the FilterBundle.

#### Too much queries

After entities hydration, Doctrine will return all the Data results with their values collection in lazy loading mode.
This means that each time you want to access values of an EAV entity, Doctrine will make an extra call to hydrate the
values collection. The OptimizedDataLoader can batch load a large number of EAV entities values collections hence 
lowering the number of database queries used to fully hydrate entities.

### Cookbook

The following examples are directly inherited from the main
[Sidus/EAVModelBundle](https://vincentchalnot.github.io/SidusEAVModelBundle/) documentation.

#### Reusing join

The EAV query API allows you to reuse an attribute join to apply multiple conditions on it through ````clone````:

For example, this will create multiple joins for the same attribute:
````php
<?php
/** @var \Sidus\EAVModelBundle\Doctrine\SingleFamilyQueryBuilder $eavQb */
$qb = $eavQb->apply(
    $eavQb->getOr([
        $eavQb->a('publicationDate')->between(new DateTime('last monday'), new DateTime()),
        $eavQb->a('publicationDate')->isNull(),
    ])
);
````

Instead you should do this:

````php
<?php
/** @var \Sidus\EAVModelBundle\Doctrine\SingleFamilyQueryBuilder $eavQb */

// We need the attribute query builder to clone it
$publicationDateAttributeQb = $eavQb->a('publicationDate');

$qb = $eavQb->apply(
    $eavQb->getOr([
        $publicationDateAttributeQb->between(new DateTime('last monday'), new DateTime()),
        (clone $publicationDateAttributeQb)->isNull(),
    ])
);
````


#### Data loading

````php
<?php
/**
 * @var \Sidus\EAVModelBundle\Model\FamilyInterface $family
 * @var \Sidus\EAVModelBundle\Doctrine\EAVFinder $eavFinder
 * @var \Sidus\EAVModelBundle\Entity\DataInterface $author
 * @var \Sidus\EAVModelBundle\Doctrine\DataLoaderInterface $dataLoader
*/
$qb = $eavFinder->getFilterByQb($family, [
    ['published', '=', true],
    ['title', 'like', 'My little %'],
]);

$qb // For example if you want to filter on a property that is not a EAV attribute:
    ->andWhere('e.updatedAt > :date')
    ->setParameter('date', new DateTime('yesterday'));

$results = $qb->getQuery()->getResult();

// SUPER IMPORTANT!!!!
$dataLoader->load($results);
````

### Fetching the repository

````php
<?php
/**
 * @var \Sidus\EAVModelBundle\Model\FamilyInterface $family
 * @var \Doctrine\ORM\EntityManagerInterface $entityManager
 * @var \CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository $dataRepository
 * @var integer $id
 * @var \Sidus\EAVModelBundle\Doctrine\DataLoaderInterface $dataLoader
*/
$dataRepository = $entityManager->getRepository($family->getDataClass());

$entity = $dataRepository->find($id);

// SUPER IMPORTANT!!!!
$dataLoader->loadSingle($entity);
````
