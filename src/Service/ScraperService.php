<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Entity\Worker;
use App\Entity\Room;
use App\Entity\Group;
use App\Entity\Subject;
use App\Entity\Schedule;
use App\Entity\User;

class ScraperService
{
    private Client $client;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->client = new Client();
        $this->entityManager = $entityManager;
    }

    public function scrapeAndSave(string $kind, string $query): array
    {
        try {
            $url =
                "https://plan.zut.edu.pl/schedule.php?kind={$kind}&query=" .
                urlencode($query);
            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            return ["error" => "Failed to fetch data: " . $e->getMessage()];
        }

        if (!$data) {
            return ["error" => "No data found"];
        }

        foreach ($data as $item) {
            if (is_array($item) && isset($item["item"])) {
                $this->saveEntity($kind, $item["item"]);
            }
        }

        return ["message" => "Data scraped and saved successfully"];
    }

    public function scrapeAndSaveSchedule(
        string $type,
        string $id,
        string $start,
        string $end
    ): array {
        try {
            $url = "";
            if ($type === "student") {
                $url =
                    "https://plan.zut.edu.pl/schedule_student.php?number=" .
                    urlencode($id);
                $url .=
                    "&start=" . urlencode($start) . "&end=" . urlencode($end);
            } else {
                $url = "https://plan.zut.edu.pl/schedule_student.php?";
                if ($type === "teacher") {
                    $url .= "teacher=" . urlencode($id);
                } elseif ($type === "room") {
                    $url .= "room=" . urlencode($id);
                }
                $url .=
                    "&start=" . urlencode($start) . "&end=" . urlencode($end);
            }

            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            return [
                "error" => "Failed to fetch schedule data: " . $e->getMessage(),
            ];
        }

        if (!$data || !is_array($data)) {
            return ["error" => "No schedule data found"];
        }

        if ($type === "student") {
            $this->saveUser($id);
            $this->deleteOldSchedule($type, $id);
            foreach ($data as $scheduleItem) {
                if (is_array($scheduleItem)) {
                    try {
                        $this->saveScheduleEntity($scheduleItem, $type, $id);
                    } catch (\InvalidArgumentException $e) {
                        continue;
                    }
                }
            }
            return ["message" => "Data scraped and saved successfully"];
        } else {
            $this->deleteOldSchedule($type, $id);
            foreach ($data as $scheduleItem) {
                if (is_array($scheduleItem)) {
                    try {
                        $this->saveScheduleEntity($scheduleItem, $type, $id);
                    } catch (\InvalidArgumentException $e) {
                        continue;
                    }
                }
            }
            return ["message" => "Data scraped and saved successfully"];
        }
    }
    private function saveEntity(string $kind, string $name): void
    {
        switch ($kind) {
            case "teacher":
                $worker = (new Worker())->setName($name);
                $this->entityManager->persist($worker);
                break;
            case "room":
                if (is_string($name) && !empty($name)) {
                    $room = (new Room())->setName($name);
                    $this->entityManager->persist($room);
                }
                break;
            case "group":
                if (is_string($name) && !empty($name)) {
                    $group = (new Group())->setName($name);
                    $this->entityManager->persist($group);
                }
                break;
            case "subject":
                $subject = (new Subject())->setTitle($name);
                $this->entityManager->persist($subject);
                break;
        }
        $this->entityManager->flush();
    }
    private function saveUser(string $nrAlbumu): void
    {
        $user = (new User())->setNrAlbumu($nrAlbumu);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
    private function deleteOldSchedule(string $type, string $id): void
    {
        $repository = $this->entityManager->getRepository(Schedule::class);
        if ($type === "student") {
            $user = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(["nrAlbumu" => $id]);
            if ($user) {
                $oldSchedules = $repository->findBy(["user" => $user]);
            }
        }
        if ($type === "teacher") {
            $teacher = $this->entityManager
                ->getRepository(Worker::class)
                ->findOneBy(["name" => $id]);
            if ($teacher) {
                $oldSchedules = $repository->findBy(["worker" => $teacher]);
            }
        }
        if ($type === "room") {
            $room = $this->entityManager
                ->getRepository(Room::class)
                ->findOneBy(["name" => $id]);
            if ($room) {
                $oldSchedules = $repository->findBy(["room" => $room]);
            }
        }
        if (isset($oldSchedules)) {
            foreach ($oldSchedules as $schedule) {
                $this->entityManager->remove($schedule);
            }
            $this->entityManager->flush();
        }
    }

    private function saveScheduleEntity(
        array $scheduleItem,
        string $type,
        string $id = null
    ): void {
        $schedule = new Schedule();
        try {
            if (
                isset($scheduleItem["title"]) &&
                is_string($scheduleItem["title"]) &&
                !empty($scheduleItem["title"])
            ) {
                $schedule->setTitle($scheduleItem["title"]);
            } else {
                return;
            }
            if (
                isset($scheduleItem["description"]) &&
                is_string($scheduleItem["description"]) &&
                !empty($scheduleItem["description"])
            ) {
                $schedule->setDescription($scheduleItem["description"]);
            }
            if (
                isset($scheduleItem["start"]) &&
                is_string($scheduleItem["start"]) &&
                !empty($scheduleItem["start"])
            ) {
                $schedule->setStartTime(new \DateTime($scheduleItem["start"]));
            } else {
                return;
            }
            if (
                isset($scheduleItem["end"]) &&
                is_string($scheduleItem["end"]) &&
                !empty($scheduleItem["end"])
            ) {
                $schedule->setEndTime(new \DateTime($scheduleItem["end"]));
            } else {
                return;
            }
            // Додаємо lesson_form
            if (
                isset($scheduleItem["lesson_form"]) &&
                is_string($scheduleItem["lesson_form"]) &&
                !empty($scheduleItem["lesson_form"])
            ) {
                $schedule->setLessonForm($scheduleItem["lesson_form"]);
            }
            // Worker
            if (
                isset($scheduleItem["worker"]) &&
                is_string($scheduleItem["worker"]) &&
                !empty($scheduleItem["worker"])
            ) {
                $workerName = $scheduleItem["worker"];
                $worker = $this->entityManager
                    ->getRepository(Worker::class)
                    ->findOneBy(["name" => $workerName]);
                if (!$worker) {
                    // If worker does not exist, create new
                    $worker = (new Worker())->setName($workerName);
                    $this->entityManager->persist($worker);
                }
                $schedule->setWorker($worker);
            }
            //Room
            if (
                isset($scheduleItem["room"]) &&
                is_string($scheduleItem["room"]) &&
                !empty($scheduleItem["room"])
            ) {
                $roomName = $scheduleItem["room"];
                $room = $this->entityManager
                    ->getRepository(Room::class)
                    ->findOneBy(["name" => $roomName]);
                if (!$room) {
                    $room = (new Room())->setName($roomName);
                    $this->entityManager->persist($room);
                }
                $schedule->setRoom($room);
            }

            //Group
            if (
                isset($scheduleItem["group_name"]) &&
                is_string($scheduleItem["group_name"]) &&
                !empty($scheduleItem["group_name"])
            ) {
                $groupName = $scheduleItem["group_name"];
                $group = $this->entityManager
                    ->getRepository(Group::class)
                    ->findOneBy(["name" => $groupName]);
                if (!$group) {
                    $group = (new Group())->setName($groupName);
                    $this->entityManager->persist($group);
                }
                $schedule->setGroup($group);
            }
            //Subject
            if (
                isset($scheduleItem["subject"]) &&
                is_string($scheduleItem["subject"]) &&
                !empty($scheduleItem["subject"])
            ) {
                $subjectTitle = $scheduleItem["subject"];
                $subject = $this->entityManager
                    ->getRepository(Subject::class)
                    ->findOneBy(["title" => $subjectTitle]);
                if (!$subject) {
                    $subject = (new Subject())->setTitle($subjectTitle);
                    $this->entityManager->persist($subject);
                }
                $schedule->setSubject($subject);
            }
            if ($type === "student" && $id) {
                $user = $this->entityManager
                    ->getRepository(User::class)
                    ->findOneBy(["nrAlbumu" => $id]);
                if ($user) {
                    $schedule->setUser($user);
                }
            }
            $this->entityManager->persist($schedule);
            $this->entityManager->flush();

        } catch (\Exception $e) {
            return;
        }
    }
}