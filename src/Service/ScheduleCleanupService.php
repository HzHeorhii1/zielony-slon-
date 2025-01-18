<?php

namespace App\Service;


use App\ORM\EntityManager;
use App\Entity\Schedule;

class ScheduleCleanupService
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function cleanupUserSchedules(): void
    {
        $schedules = $this->entityManager->getRepository(Schedule::class)->findAll();

        foreach ($schedules as $schedule) {
            if($schedule->getUserId()){
                $this->entityManager->remove($schedule);
            }

        }

        $this->entityManager->flush();
    }
}