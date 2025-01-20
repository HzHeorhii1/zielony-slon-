<?php

namespace App\Controller;

use App\ORM\EntityManager;
use App\Utils\JsonResponse;
use App\Utils\Request;
use App\Utils\SerializerInterface;
use App\Utils\JsonEncoder;
use App\Utils\ObjectNormalizer;
use App\Utils\Serializer;

class EntityController
{
    private EntityManager $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function createEntity(Request $request, string $entity): JsonResponse
    {
        $entityClass = $this->getEntityClass($entity);

        if (!$entityClass) {
            return new JsonResponse(["error" => "Invalid entity type."], 400);
        }

        try {
            $data = json_decode($request->getContent(), true);
            if(json_last_error() !== JSON_ERROR_NONE || !$data){
                return new JsonResponse(['error'=>'Invalid json'], 400);
            }

            $newEntity = $this->serializer->denormalize($data, $entityClass);

            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();

            $jsonContent =  $this->serializer->serialize($newEntity,'json');

            return new JsonResponse([
                'message' =>'Entity created succesfully',
                'data'=> json_decode($jsonContent,true)
            ],201);

        } catch (\Exception $e) {
            return new JsonResponse(['error' =>'Failed to create an entity '. $e->getMessage()],500);
        }
    }

    public function getEntity(string $entity, int $id): JsonResponse
    {
        $entityClass = $this->getEntityClass($entity);
        if (!$entityClass) {
            return new JsonResponse(["error" => "Invalid entity type."], 400);
        }
        $repository = $this->entityManager->getRepository($entityClass);
        $entity = $repository->find($id);

        if (!$entity) {
            return new JsonResponse(["error" => "Entity not found"], 404);
        }

        $jsonContent =  $this->serializer->serialize($entity,'json');
        return new JsonResponse(json_decode($jsonContent,true),200);
    }

    public function updateEntity(Request $request, string $entity, int $id): JsonResponse
    {
        $entityClass = $this->getEntityClass($entity);

        if (!$entityClass) {
            return new JsonResponse(["error" => "Invalid entity type."], 400);
        }

        $repository = $this->entityManager->getRepository($entityClass);
        $entityToUpdate = $repository->find($id);

        if (!$entityToUpdate) {
            return new JsonResponse(["error" => "Entity not found"], 404);
        }
        try {
            $data = json_decode($request->getContent(), true);
            if(json_last_error() !== JSON_ERROR_NONE || !$data){
                return new JsonResponse(['error'=>'Invalid json'], 400);
            }
            $updatedEntity = $this->serializer->denormalize($data,$entityClass, null, ['object_to_populate'=>$entityToUpdate]);
            $this->entityManager->persist($updatedEntity);
            $this->entityManager->flush();

            $jsonContent =  $this->serializer->serialize($updatedEntity,'json');
            return new JsonResponse([
                'message'=>'Entity updated succesfully',
                'data' =>json_decode($jsonContent,true)

            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' =>'Failed to update entity '. $e->getMessage()],500);
        }

    }

    public function deleteEntity(string $entity, int $id): JsonResponse
    {
        $entityClass = $this->getEntityClass($entity);
        if (!$entityClass) {
            return new JsonResponse(["error" => "Invalid entity type."], 400);
        }
        $repository = $this->entityManager->getRepository($entityClass);
        $entityToDelete = $repository->find($id);
        if (!$entityToDelete) {
            return new JsonResponse(["error" => "Entity not found"], 404);
        }
        try {
            $this->entityManager->remove($entityToDelete);
            $this->entityManager->flush();

            return  new JsonResponse(['message' => 'Entity deleted'], 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to delete an entity ' . $e->getMessage()], 500);
        }
    }

    private function getEntityClass(string $entity): ?string
    {
        $entityClassMap = [
            'group' => 'App\Entity\Group',
            'room' => 'App\Entity\Room',
            'schedule' => 'App\Entity\Schedule',
            'subject' => 'App\Entity\Subject',
            'user' => 'App\Entity\User',
            'worker' => 'App\Entity\Worker',
        ];
        return $entityClassMap[$entity] ?? null;
    }
}