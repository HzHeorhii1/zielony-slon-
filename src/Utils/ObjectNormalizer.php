<?php

namespace App\Utils;

use ReflectionClass;
use ReflectionProperty;

// 4 transition php objects into arrays(normalisation) and from 'em*(denormalisation)
class ObjectNormalizer
{
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        $data = [];
        $reflection = new ReflectionClass($object);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            if (is_object($value)) {
                if (method_exists($value, 'getId') ) {
                    $data[$property->getName()] = $value->getId();
                }
                else {
                    $data[$property->getName()] = $this->normalize($value);
                }
            } else {
                $data[$property->getName()] = $value;
            }
        }

        return $data;
    }
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $reflection = new ReflectionClass($type);
        $object = $context['object_to_populate'] ?? $reflection->newInstance();

        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);
                $property->setAccessible(true);
                if ($property->getType() && !$property->getType()->isBuiltin()) {
                    $targetType = $property->getType()->getName();
                    if(is_array($value)){
                        $property->setValue($object, $this->denormalize($value,$targetType));
                    }
                    else {
                        $property->setValue($object, $value);
                    }


                }
                else{
                    $property->setValue($object, $value);
                }

            }
        }

        return $object;

    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return is_object($data) && !$data instanceof \DateTimeInterface ;
    }
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return class_exists($type);
    }
}