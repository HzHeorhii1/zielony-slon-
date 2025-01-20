<?php

namespace App\Controller;

use App\Utils\JsonResponse;
use App\Utils\Request;

class ExternalScheduleController
{
    public function getExternalSchedule(Request $request): JsonResponse
    {

        $baseUrl = 'https://plan.zut.edu.pl/schedule_student.php';
        $queryParams = $request->query->all();
        $queryString = http_build_query($queryParams);

        try {
            $url = $baseUrl . '?' . $queryString;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                throw new \Exception("HTTP Error: $httpCode");
            }

            $data = json_decode($response, true);
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from plan.zut.edu.pl");
            }
            curl_close($ch);
            $formattedData = $this->formatExternalScheduleData($data);
            return new JsonResponse($formattedData);

        }  catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    private function formatExternalScheduleData(array $scheduleData): array
    {
        $formattedSchedule = [];
        $lessonTypeColors = [
            'laboratorium' => '#1A8238',
            'wykład' => '#247C84',
            'projekt' => '#555500',
            'egzamin' => '#007BB0',
            'audytoryjne' => '#007BB0',
            'rektorskie' => '#1A8238',
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