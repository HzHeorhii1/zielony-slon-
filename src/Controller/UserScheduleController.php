<?php

namespace App\Controller;

use App\Service\ApiService;
use App\Service\ScraperService;
use App\Utils\JsonResponse;
use App\Utils\Request;
use App\Utils\Route;

class UserScheduleController
{
    private ApiService $apiService;
    private ScraperService $scraperService;

    public function __construct(
        ApiService $apiService,
        ScraperService $scraperService
    ) {
        $this->apiService = $apiService;
        $this->scraperService = $scraperService;
    }

    #[Route("/api/user/schedule", methods: ["GET"])]
    public function getUserSchedule(Request $request): JsonResponse
    {
        $studentId = $request->query["id"] ?? null;
        $start = $request->query["start"] ?? null;
        $end = $request->query["end"] ?? null;
        $kind = $request->query["kind"] ?? null;
        $query = $request->query["query"] ?? null;
        $teacher = $request->query["teacher"] ?? null;
        $room = $request->query["room"] ?? null;
        $group = $request->query["group"] ?? null;

        if (!$studentId && ($teacher || $room || $group || $query)) {

            $baseUrl = 'https://plan.zut.edu.pl/schedule_student.php';

            $params = array_filter([
                "teacher" => $teacher,
                "room" => $room,
                "group" => $group,
                "subject" => $query,
                "start" => $start,
                "end" => $end,
            ]);

            $url = $baseUrl . '?' . http_build_query($params);


            try {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpCode !== 200) {
                    throw new \Exception("HTTP Error: $httpCode");
                }
                $data = json_decode($response, true);
                curl_close($ch);

                if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Invalid JSON response from plan.zut.edu.pl");
                }

                $formattedData = $this->formatExternalScheduleData($data);
                return new JsonResponse($formattedData);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Failed to fetch schedule from plan.zut.edu.pl: ' . $e->getMessage()], 500);
            }
        }


        if (!$studentId) {
            return new JsonResponse(["error" => "Student ID is required"], 400);
        }

        $schedules = $this->apiService->getScheduleByStudent(
            $studentId,
            $start,
            $end,
            $kind,
            $query,
            $teacher,
            $room,
            $group
        );

        if (empty($schedules)) {
            $scrapeResult = $this->scraperService->scrapeAndSaveSchedule(
                "student",
                $studentId,
                $start,
                $end
            );

            if (isset($scrapeResult["error"])) {
                return new JsonResponse(
                    [
                        "error" =>
                            "Failed to fetch schedule from external API: " .
                            $scrapeResult["error"],
                    ],
                    500
                );
            }
            $schedules = $this->apiService->getScheduleByStudent(
                $studentId,
                $start,
                $end,
                $kind,
                $query,
                $teacher,
                $room,
                $group
            );
            return new JsonResponse($schedules);
        }

        return new JsonResponse($schedules);
    }
    private function formatExternalScheduleData(array $scheduleData): array
    {
        $formattedSchedule = [];
        $lessonTypeColors = [
            'laboratorium' => '#1A8238',
            'wykład' => '#494949',
            'projekt' => '#555500',
            'egzamin' => '#007BB0',
            'audytoryjne' => '#007BB0',
            'lektorat' => '#C44F00'
        ];

        foreach ($scheduleData as $item) {
            if (isset($item['start'], $item['end'])) {

                $startTime = new \DateTime($item['start']);
                $endTime = new \DateTime($item['end']);
                $date = $startTime->format('Y-m-d');

                $lessonForm = strtolower($item['lesson_form'] ?? '');
                $color = $lessonTypeColors[$lessonForm] ?? '#3788d8'; // default color if lesson type is not found

                $formattedItem = [
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'startTime' =>  $startTime->format('Y-m-d H:i:s'),
                    'endTime' => $endTime->format('Y-m-d H:i:s'),
                    'worker' => isset($item['worker_title']) ? ['name' => $item['worker_title']] : null,
                    'room' => isset($item['room']) ? ['name' => $item['room']] : null,
                    'group' => isset($item['group_name']) ? ['name' => $item['group_name']] : null,
                    'subject' => isset($item['subject']) ? ['title' => $item['subject']] : null,
                    'lesson_form' => $lessonForm,
                    'color' => $color,

                ];
                if(!isset($formattedSchedule[$date]))
                {
                    $formattedSchedule[$date] = [];
                }
                $formattedSchedule[$date][] = $formattedItem;
            }
        }
        return $formattedSchedule;
    }
}