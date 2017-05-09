<?php
namespace CleverAge\EAVManager\ImportBundle\Source;


use CleverAge\EAVManager\ImportBundle\Source\DataSourceInterface;

class StaticSource implements DataSourceInterface
{
    /** @var array */
    protected $data;

    /**
     * StaticSource constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

}
