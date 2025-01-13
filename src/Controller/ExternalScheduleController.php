<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ExternalScheduleController
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getExternalSchedule(Request $request): JsonResponse
    {

        $baseUrl = 'https://plan.zut.edu.pl/schedule_student.php';
        $queryParams = $request->query->all();
        $queryString = http_build_query($queryParams);

        try {
            $url = $baseUrl . '?' . $queryString;
            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from plan.zut.edu.pl");
            }
            $formattedData = $this->formatExternalScheduleData($data);
            return new JsonResponse($formattedData);

        } catch (GuzzleException $e) {
            return new JsonResponse(['error' => 'Failed to fetch schedule from plan.zut.edu.pl: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
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