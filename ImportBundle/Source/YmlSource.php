<?php

namespace CleverAge\EAVManager\ImportBundle\Source;

use Symfony\Component\Yaml\Yaml;

/**
 * @TODO describe class usage
 */
class YmlSource implements DataSourceInterface
{

    /** @var string */
    protected $filePath;

    /**
     * YmlSource constructor.
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return Yaml::parse(file_get_contents($this->filePath));
    }


}
