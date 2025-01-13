<?php

namespace App\Utils;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class Container
{
    private array $services = [];

    public function __construct(EntityManager $entityManager)
    {
        $this->services["Doctrine\ORM\EntityManager"] = $entityManager;
        $this->services[EntityManagerInterface::class] = $entityManager;
    }

    public function get(string $id): mixed
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (class_exists($id)) {
            $service = $this->createService($id);
            $this->services[$id] = $service;
            return $service;
        }

        throw new \Exception("Service with id '$id' does not exist");
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]) || class_exists($id);
    }

    private function createService(string $className): object
    {
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $className();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencyType = $parameter->getType();

            if ($dependencyType === null) {
                throw new \Exception(
                    "Can't resolve non-typed dependency for service '$className'"
                );
            }
            if ($dependencyType->isBuiltin() === false) {
                $dependencyName = $dependencyType->getName();
                $dependencies[] = $this->get($dependencyName);
            } else {
                throw new \Exception(
                    "Can't resolve builtin dependency for service '$className'"
                );
            }
        }

        return new $className(...$dependencies);
    }
}
