<?php

namespace App\ORM;

use App\ORM\Mapping\ClassMetadata;
use PDO;
class QueryBuilder
{
    private EntityManager $entityManager;
    private ClassMetadata $classMetadata;
    private string $select = '*';
    private array $from = [];
    private array $joins = [];
    private array $where = [];
    private array $params = [];
    private array $orderBy = [];


    public function __construct(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $this->entityManager = $entityManager;
        $this->classMetadata = $classMetadata;
    }

    public function select(string $select): self
    {
        $this->select = $select;
        return $this;
    }

    public function from(string $from, string $alias): self
    {
        $classMetadata = $this->entityManager->getClassMetadata($from);
        $tableName = $classMetadata->getTableName();
        $this->from = [$tableName, $alias];
        return $this;
    }
    public function join(string $joinTable, string $joinAlias, string $on): self
    {
        $classMetadata = $this->entityManager->getClassMetadata($joinTable);
        $tableName = $classMetadata->getTableName();
        $this->joins[] = ['joinTable'=>$tableName, 'joinAlias'=>$joinAlias, 'on'=>$on];
        return $this;
    }

    public function where(string $condition): self
    {
        $this->where[] = $condition;
        return $this;
    }

    public function andWhere(string $condition): self
    {
        $this->where[] = $condition;
        return $this;
    }
    public function setParameter(string $key, $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function orderBy(string $field, string $direction): self
    {
        $this->orderBy[$field] = $direction;
        return $this;
    }

    public function getQuery(): array
    {
        $tableName = $this->from[0];
        $alias = $this->from[1];
        $selectClause = $this->select === '*' ? "{$alias}.*" : $this->select;
        $sql = "SELECT {$selectClause} FROM {$tableName} AS {$alias}";

        foreach ($this->joins as $join) {
            $joinTable = $join['joinTable'];
            $joinAlias = $join['joinAlias'];
            $onClause = $join['on'];

            $sql .= " JOIN {$joinTable} AS {$joinAlias} ON {$onClause}";
        }
        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }

        if (!empty($this->orderBy)) {
            $orderClauses = [];
            foreach ($this->orderBy as $field => $direction) {
                $orderClauses[] = "$field $direction";
            }
            $sql .= " ORDER BY " . implode(", ", $orderClauses);
        }


        $connection = $this->entityManager->getConnection();
        $stmt = $connection->prepare($sql);

        foreach ($this->params as $key => $value){
            if ($value instanceof \DateTime){
                $stmt->bindValue(":{$key}",$value->format('Y-m-d H:i:s'));
            }
            else {
                $stmt->bindValue(":{$key}",$value);
            }
        }
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entityName =  $this->classMetadata->getEntityName();
        $entities = [];
        foreach ($results as $result){
            $entity = new $entityName();
            foreach ($result as $field => $value){
                $property = new \ReflectionProperty($entity, $field);
                $property->setAccessible(true);
                if ($property->getType()
                    && $property->getType()->getName() === \DateTimeInterface::class
                    && is_string($value)) {
                    $value = new \DateTime($value);
                }
                $property->setValue($entity, $value);

            }
            $entities[] = $entity;

        }

        return $entities;
    }
}