<?php

namespace App\ORM\Mapping;

use ReflectionClass;
use ReflectionProperty;

class ClassMetadata
{
    private string $entityName;
    private string $tableName;
    private array $fields = [];
    private array $identifier = [];
    private ReflectionClass $reflection;

    public function __construct(string $entityName)
    {
        $this->entityName = $entityName;
        $this->reflection = new ReflectionClass($entityName);
        $this->loadMetadata();
    }

    private function loadMetadata(): void
    {
        $tableAttribute = $this->reflection->getAttributes('App\ORM\Mapping\Table')[0] ?? null;
        if (!$tableAttribute) {
            throw new \Exception("3ntity $this->entityName 's missing table mapping");
        }

        $this->tableName = $tableAttribute->getArguments()['name'];

        $properties = $this->reflection->getProperties();
        foreach ($properties as $property) {
            $columnAttribute = $property->getAttributes('App\ORM\Mapping\Column')[0] ?? null;
            if($columnAttribute)
            {

                $field = $property->getName();
                $this->fields[$field] = $columnAttribute->getArguments()['type'] ?? 'string';

                $idAttribute = $property->getAttributes('App\ORM\Mapping\Id')[0] ?? null;
                if($idAttribute) {
                    $this->identifier[] = $field;
                }
            }

        }
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
    public function getIdentifier(): array
    {
        return $this->identifier;
    }

    public function getFieldValue(object $entity, string $field)
    {
        $property = new ReflectionProperty($entity, $field);
        $property->setAccessible(true);
        return $property->getValue($entity);
    }

    public function setFieldValue(object $entity, string $field, $value): void
    {
        $property = new ReflectionProperty($entity, $field);
        $property->setAccessible(true);

        if ($property->getType()
            && $property->getType()->getName() === \DateTimeInterface::class
            && is_string($value)) {
            $value = new \DateTime($value);
        }
        $property->setValue($entity, $value);
    }

}