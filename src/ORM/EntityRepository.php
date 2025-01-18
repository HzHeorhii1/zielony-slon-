<?php

namespace App\ORM;

use PDO;
use App\ORM\Mapping\ClassMetadata;
use App\ORM\QueryBuilder;
class EntityRepository
{
    private EntityManager $entityManager;
    private ClassMetadata $classMetadata;


    public function __construct(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $this->entityManager = $entityManager;
        $this->classMetadata = $classMetadata;
    }

    public function find(int $id): ?object
    {
        $tableName = $this->classMetadata->getTableName();
        $idField = $this->classMetadata->getIdentifier()[0];
        $sql = "SELECT * FROM $tableName WHERE $idField = ?";
        try {
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return $this->createEntityFromData($result);
            }
            return null;

        } catch (\PDOException $e) {
            throw new \Exception("faild to find entity: " . $e->getMessage());
        }
    }

    public function findOneBy(array $criteria): ?object
    {
        $tableName = $this->classMetadata->getTableName();
        $whereClauses = [];
        $params = [];

        foreach ($criteria as $field => $value) {
            $whereClauses[] = "$field = ?";
            $params[] = $value;
        }

        $whereClause = implode(" AND ", $whereClauses);

        $sql = "SELECT * FROM $tableName WHERE $whereClause LIMIT 1";
        try {
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return $this->createEntityFromData($result);
            }
            return null;
        } catch (\PDOException $e) {
            throw new \Exception("Failed to find entity by criteria: " . $e->getMessage());
        }
    }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $tableName = $this->classMetadata->getTableName();
        $whereClauses = [];
        $params = [];

        foreach ($criteria as $field => $value) {
            $whereClauses[] = "$field = ?";
            $params[] = $value;
        }

        $whereClause = implode(" AND ", $whereClauses);
        $sql = "SELECT * FROM $tableName WHERE $whereClause";
        if ($orderBy) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $orderClauses[] = "$field $direction";
            }
            $sql .= " ORDER BY " . implode(", ", $orderClauses);
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        if ($offset) {
            $sql .= " OFFSET $offset";
        }
        try {
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(fn ($data) => $this->createEntityFromData($data), $results);
        } catch (\PDOException $e) {
            throw new \Exception("faild to find entities by criteria: " . $e->getMessage());
        }
    }

    public function findAll(): array
    {
        $tableName = $this->classMetadata->getTableName();
        $sql = "SELECT * FROM $tableName";
        try {
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(fn ($data) => $this->createEntityFromData($data), $results);
        } catch (\PDOException $e) {
            throw new \Exception("can not find all entities: " . $e->getMessage());
        }
    }
    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->entityManager, $this->classMetadata);
    }

    private function createEntityFromData(array $data): object
    {
        $entityName = $this->classMetadata->getEntityName();
        $entity = new $entityName();

        foreach ($data as $field => $value) {
            $this->classMetadata->setFieldValue($entity, $field, $value);
        }
        return $entity;
    }
}