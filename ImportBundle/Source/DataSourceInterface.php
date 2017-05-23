<?php

namespace CleverAge\EAVManager\ImportBundle\Source;

/**
 * Represent a data source to use for import
 * It should return data as close as possible from the original source. Transformations shall be done later.
 *
 * @deprecated since it will be moved to the process bundle
 */
interface DataSourceInterface
{
    /**
     * Should return an array of entities to import :
     * [
     *     reference => [
     *         old_attribute_code => value,
     *         old_attribute_code => value,
     *         old_attribute_code => value,
     *     ],
     *     reference => [
     *         old_attribute_code => value,
     *         old_attribute_code => value,
     *         old_attribute_code => value,
     *     ],
     * ]
     *
     * @TODO manage batching
     *
     * @return array
     */
    public function getData(): array;
}
