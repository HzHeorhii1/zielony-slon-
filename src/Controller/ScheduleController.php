<?php

namespace App\Controller;

use App\Service\ApiService;
use App\Utils\JsonResponse;
use App\Utils\Request;
use App\Utils\Route;

class ScheduleController
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    #[Route("/api/schedule/teacher", methods: ["GET"])]
    public function getScheduleByTeacher(Request $request): JsonResponse
    {
        $teacherId = $request->query->get("id");
        if (!$teacherId) {
            return new JsonResponse(["error" => "Teacher ID is required"], 400);
        }
        $schedules = $this->apiService->getScheduleByTeacher($teacherId);
        return new JsonResponse($schedules);
    }

    #[Route("/api/schedule/room", methods: ["GET"])]
    public function getScheduleByRoom(Request $request): JsonResponse
    {
        $roomId = $request->query->get("id");
        if (!$roomId) {
            return new JsonResponse(["error" => "Room ID is required"], 400);
        }
        $schedules = $this->apiService->getScheduleByRoom($roomId);
        return new JsonResponse($schedules);
    }

    #[Route("/api/schedule/group", methods: ["GET"])]
    public function getScheduleByGroup(Request $request): JsonResponse
    {
        $groupId = $request->query->get("id");
        if (!$groupId) {
            return new JsonResponse(["error" => "Group ID is required"], 400);
        }
        $schedules = $this->apiService->getScheduleByGroup($groupId);
        return new JsonResponse($schedules);
    }
}