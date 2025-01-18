<?php
namespace App\Utils;

class Serializer implements SerializerInterface
{
    private array $encoders;
    private array $normalizers;

    public function __construct(array $normalizers = [], array $encoders = [])
    {
        $this->normalizers = $normalizers;
        $this->encoders = $encoders;
    }

    public function serialize(mixed $data, string $format, array $context = []): string
    {
        $normalizedData = $this->normalize($data, $format,$context);
        foreach ($this->encoders as $encoder) {
            if($encoder->supportsEncoding($format)){
                return $encoder->encode($normalizedData,$format,$context);
            }
        }
        throw new \Exception('No encoder found for format '.$format);
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        foreach ($this->normalizers as $normalizer){
            if($normalizer->supportsNormalization($object, $format, $context)){
                return $normalizer->normalize($object,$format,$context);
            }
        }
        throw new \Exception('No normalizer found to support object of type '. gettype($object));
    }

    public function deserialize(string $data, string $type, string $format, array $context = []): mixed
    {
        foreach ($this->encoders as $encoder) {
            if($encoder->supportsDecoding($format)){
                $decodedData =   $encoder->decode($data, $format, $context);
                return $this->denormalize($decodedData, $type, $format, $context);
            }

        }
        throw new \Exception('No encoder found to support format '.$format);
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        foreach ($this->normalizers as $normalizer){
            if($normalizer->supportsDenormalization($data, $type, $format, $context)){
                return $normalizer->denormalize($data, $type, $format, $context);
            }
        }
        throw new \Exception('No normalizer found to support type '.$type);
    }
}