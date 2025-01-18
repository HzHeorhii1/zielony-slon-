<?php

namespace App\ORM;

use PDO;
use PDOException;
use App\ORM\EntityRepository;
use App\ORM\Mapping\ClassMetadata;

class EntityManager
{
    private PDO $connection;
    private array $repositories = [];
    private array $metadata = [];

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getRepository(string $entityName): EntityRepository
    {
        if (!isset($this->repositories[$entityName])) {
            $classMetadata = $this->getClassMetadata($entityName);
            $this->repositories[$entityName] = new EntityRepository($this, $classMetadata);
        }

        return $this->repositories[$entityName];
    }

    public function persist(object $entity): void
    {
        $classMetadata = $this->getClassMetadata(get_class($entity));
        $tableName = $classMetadata->getTableName();
        $fields = $classMetadata->getFields();
        $idField = $classMetadata->getIdentifier()[0];
        $idValue = $classMetadata->getFieldValue($entity, $idField);



        if($idValue){
            $this->update($entity, $classMetadata);
            return;
        }

        $fieldNames = array_keys($fields);
        $fieldValues = array_map(
            fn($field) => $classMetadata->getFieldValue($entity, $field) instanceof \DateTimeInterface
                ? $classMetadata->getFieldValue($entity, $field)->format('Y-m-d H:i:s')
                : $classMetadata->getFieldValue($entity, $field),
            $fieldNames
        );


        $placeholders = implode(', ', array_fill(0, count($fieldNames), '?'));
        $columns = implode(', ', $fieldNames);

        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($fieldValues);
            $property = new \ReflectionProperty($entity, $idField);
            $property->setAccessible(true);
            $property->setValue($entity,(int)$this->connection->lastInsertId());
        } catch (PDOException $e) {
            throw new \Exception("faild to persist entity: " . $e->getMessage());
        }

    }

    private function update(object $entity, ClassMetadata $classMetadata): void
    {
        $tableName = $classMetadata->getTableName();
        $fields = $classMetadata->getFields();
        $idField = $classMetadata->getIdentifier()[0];
        $idValue = $classMetadata->getFieldValue($entity, $idField);
        $fieldNames = array_keys($fields);
        $fieldValues = array_map(
            fn($field) => $classMetadata->getFieldValue($entity, $field) instanceof \DateTimeInterface
                ? $classMetadata->getFieldValue($entity, $field)->format('Y-m-d H:i:s')
                : $classMetadata->getFieldValue($entity, $field),
            $fieldNames
        );


        $setClauses = implode(', ', array_map(fn($field) => "$field = ?", $fieldNames));
        $sql = "UPDATE $tableName SET $setClauses WHERE $idField = ?";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_merge($fieldValues, [$idValue]));
        } catch (PDOException $e) {
            throw new \Exception("faild to update entity: " . $e->getMessage());
        }

    }

    public function remove(object $entity): void
    {
        $classMetadata = $this->getClassMetadata(get_class($entity));
        $tableName = $classMetadata->getTableName();
        $idField = $classMetadata->getIdentifier()[0];
        $idValue = $classMetadata->getFieldValue($entity, $idField);

        $sql = "DELETE FROM $tableName WHERE $idField = ?";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idValue]);
        } catch (PDOException $e) {
            throw new \Exception("faild to remove entity: " . $e->getMessage());
        }
    }

    public function flush(): void
    {
        // aber hier wird nichts gemacht, so let it be empty
    }

    public function getClassMetadata(string $entityName): ClassMetadata
    {
        if (!isset($this->metadata[$entityName])) {
            $this->metadata[$entityName] = new ClassMetadata($entityName);
        }
        return $this->metadata[$entityName];
    }

}