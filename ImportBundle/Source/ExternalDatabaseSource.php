<?php

namespace CleverAge\EAVManager\ImportBundle\Source;

use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Fetch import data from an external database
 */
class ExternalDatabaseSource implements DataSourceInterface
{

    /** @var  RegistryInterface */
    protected $doctrine;

    /** @var  string */
    protected $connectionName;

    /** @var  string */
    protected $tableName;

    /** @var  string */
    protected $idFieldName;

    /**
     * ExternalDatabaseSource constructor.
     * @param RegistryInterface $doctrine
     * @param string            $connectionName
     * @param string            $tableName
     * @param string            $idFieldName
     */
    public function __construct(
        RegistryInterface $doctrine,
        $connectionName,
        $tableName,
        $idFieldName
    ) {
        $this->doctrine = $doctrine;
        $this->connectionName = $connectionName;
        $this->tableName = $tableName;
        $this->idFieldName = $idFieldName;
    }

    /**
     * @TODO may be there should not be a custom query but an overridable method...
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function getData()
    {
        /** @var Connection $connection */
        $connection = $this->doctrine->getConnection($this->connectionName);

        $data = $connection->fetchAll($this->getQuery());
        $dataSource = [];

        foreach ($data as $item) {
            if (!array_key_exists($this->idFieldName, $item)) {
                throw new \InvalidArgumentException(
                    "Column {$this->idFieldName} does not exists in table {$this->tableName}"
                );
            }

            if (array_key_exists($item[$this->idFieldName], $dataSource)) {
                throw new \InvalidArgumentException(
                    "Reference {$item[$this->idFieldName]} is not unique in column {$this->idFieldName} of table {$this->tableName}"
                );
            }

            $dataSource[$item[$this->idFieldName]] = $item;
        }

        return $dataSource;
    }

    /**
     * Extensible SQL query used to fetch data
     *
     * @return string
     */
    protected function getQuery()
    {
        return "SELECT * from `{$this->tableName}`";
    }
}
