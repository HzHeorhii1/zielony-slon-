<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ApiService
{
    private EntityManagerInterface $entityManager;
    private ScraperService $scraperService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScraperService $scraperService
    ) {
        $this->entityManager = $entityManager;
        $this->scraperService = $scraperService;
    }

    public function getScheduleByStudent(
        int $studentId,
        ?string $start = null,
        ?string $end = null,
        ?string $kind = null,
        ?string $query = null,
        ?string $teacher = null,
        ?string $room = null,
        ?string $group = null
    ): array {
        $user = $this->entityManager
            ->getRepository("App\Entity\User")
            ->findOneBy(["nrAlbumu" => $studentId]);

        if (!$user) {
            return [];
        }
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("s")
            ->from("App\Entity\Schedule", "s")
            ->where("s.user = :user")
            ->setParameter("user", $user)
            ->orderBy("s.startTime", "ASC");
        if ($teacher) {
            $qb->join("s.worker", "w")
                ->andWhere("w.name = :teacher")
                ->setParameter("teacher", $teacher);
        }
        if ($room) {
            $qb->join("s.room", "r")
                ->andWhere("r.name = :room")
                ->setParameter("room", $room);
        }
        if ($group) {
            $qb->join("s.group", "g")
                ->andWhere("g.name = :group")
                ->setParameter("group", $group);
        }

        if ($start && $end) {
            try {
                // Normalize timezones
                $start = $this->normalizeTimezone($start);
                $end = $this->normalizeTimezone($end);

                // Validate the format of the dates
                $startDate = \DateTime::createFromFormat(
                    "Y-m-d\TH:i:sO",
                    $start
                );
                $endDate = \DateTime::createFromFormat("Y-m-d\TH:i:sO", $end);

                if (!$startDate || !$endDate) {
                    throw new \Exception(
                        "Invalid date format. Ensure the dates are in the correct ISO 8601 format."
                    );
                }

                $qb->andWhere("s.startTime >= :start")
                    ->andWhere("s.startTime <= :end")
                    ->setParameter("start", $startDate)
                    ->setParameter("end", $endDate);
            } catch (\Exception $e) {
                return ["error" => "Date parsing error: " . $e->getMessage()];
            }
        }

        if ($kind === "subject" && $query) {
            $qb->join("s.subject", "subj")
                ->andWhere("subj.title = :query")
                ->setParameter("query", $query);
        }

        $schedules = $qb->getQuery()->getResult();
        $result = [];
        $lessonTypeColors = [
            'laboratorium' => '#1A8238',
            'wykład' => '#247C84',
            'projekt' => '#555500',
            'egzamin' => '#007BB0',
            'audytoryjne' => '#007BB0',
            'rektorskie' => '#1A8238',
            'lektorat' => '#C44F00'
        ];
        foreach ($schedules as $schedule) {
            $date = $schedule->getStartTime()->format("Y-m-d");
            if (!isset($result[$date])) {
                $result[$date] = [];
            }
            $formattedSchedule = $this->formatScheduleData($schedule);

            $lessonForm = strtolower($formattedSchedule['lesson_form'] ?? '');

            $color = $lessonTypeColors[$lessonForm] ?? '#3788d8'; // default color if lesson type is not found
            $formattedSchedule['color'] = $color;


            $result[$date][] = $formattedSchedule;
        }

        return $result;
    }
    private function normalizeTimezone(string $date): string
    {
        // Check if the timezone format is already in "+HHMM" format
        if (preg_match('/[+-]\d{4}$/', $date)) {
            return $date; // Already normalized
        }
        // Replace "+02:00" or similar with "+0200"
        return preg_replace('/([+-]\d{2}):(\d{2})$/', '$1$2', $date);
    }

    private function validateISO8601Date(string $date): bool
    {
        $parsedDate = \DateTime::createFromFormat("Y-m-d\TH:i:sO", $date);
        return $parsedDate !== false;
    }
    private function formatScheduleData($schedule): array
    {
        return [
            "id" => $schedule->getId(),
            "title" => $schedule->getTitle(),
            "description" => $schedule->getDescription(),
            "startTime" => $schedule->getStartTime()->format("Y-m-d H:i:s"),
            "endTime" => $schedule->getEndTime()->format("Y-m-d H:i:s"),
            "lesson_form"=>$schedule->getLessonForm(),
            "worker" => $schedule->getWorker()
                ? [
                    "id" => $schedule->getWorker()->getId(),
                    "name" => $schedule->getWorker()->getName(),
                ]
                : null,
            "room" => $schedule->getRoom()
                ? [
                    "id" => $schedule->getRoom()->getId(),
                    "name" => $schedule->getRoom()->getName(),
                ]
                : null,
            "group" => $schedule->getGroup()
                ? [
                    "id" => $schedule->getGroup()->getId(),
                    "name" => $schedule->getGroup()->getName(),
                ]
                : null,
            "subject" => $schedule->getSubject()
                ? [
                    "id" => $schedule->getSubject()->getId(),
                    "title" => $schedule->getSubject()->getTitle(),
                ]
                : null,
        ];
    }
}