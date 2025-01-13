<?php

namespace App\Controller;

use App\Service\ScraperService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ScraperController
{
    private ScraperService $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }
    #[Route("/api/scrape", methods: ["GET"])]
    public function scrapeData(Request $request): JsonResponse
    {
        $kind = $request->query->get("kind");
        $query = $request->query->get("query");

        if (!$kind || !$query) {
            return new JsonResponse(
                ["error" => "Kind and query parameters are required"],
                400
            );
        }
        $result = $this->scraperService->scrapeAndSave($kind, $query);
        return new JsonResponse($result);
    }
    #[Route("/api/scrape/schedule", methods: ["GET"])]
    public function scrapeScheduleData(Request $request): JsonResponse
    {
        $type = $request->query->get("type");
        $id = $request->query->get("id");
        $start = $request->query->get("start");
        $end = $request->query->get("end");
        if (!$type || !$id || !$start || !$end) {
            return new JsonResponse(
                ["error" => "Type, id, start and end parameters are required"],
                400
            );
        }
        $result = $this->scraperService->scrapeAndSaveSchedule(
            $type,
            $id,
            $start,
            $end
        );
        return new JsonResponse($result);
    }
}
