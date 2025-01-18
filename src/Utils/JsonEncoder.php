<?php
namespace App\Utils;

class JsonEncoder
{
    public function encode(mixed $data, string $format = null, array $context = []): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function decode(string $data, string $format = null, array $context = []): mixed
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    public function supportsEncoding(string $format): bool
    {
        return $format === 'json';
    }
    public function supportsDecoding(string $format): bool
    {
        return $format === 'json';
    }
}