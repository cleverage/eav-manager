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

    /** @var  string */
    protected $customQuery;

    /**
     * ExternalDatabaseSource constructor.
     * @param RegistryInterface $doctrine
     * @param string            $connectionName
     * @param string            $tableName
     * @param string            $idFieldName
     * @param string            $customQuery
     */
    public function __construct(
        RegistryInterface $doctrine,
        $connectionName,
        $tableName,
        $idFieldName,
        $customQuery = null
    ) {
        $this->doctrine = $doctrine;
        $this->connectionName = $connectionName;
        $this->query = $tableName;
        $this->idFieldName = $idFieldName;
        $this->customQuery = $customQuery;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function getData()
    {
        /** @var Connection $connection */
        $connection = $this->doctrine->getConnection($this->connectionName);

        $query = $this->customQuery ?? "SELECT * from `{$this->tableName}`";

        $data = $connection->fetchAll($query);
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
}
