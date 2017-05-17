<?php

namespace CleverAge\EAVManager\ImportBundle\Source;

/**
 * Simple container for a static array.
 */
class StaticSource implements DataSourceInterface
{
    /** @var array */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
