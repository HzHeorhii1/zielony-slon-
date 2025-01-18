<?php

namespace App\Utils;

interface SerializerInterface
{
    public function serialize(mixed $data, string $format, array $context = []): string;
    public function deserialize(string $data, string $type, string $format, array $context = []): mixed;
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed;
    public function normalize(mixed $object, string $format = null, array $context = []): array;
}