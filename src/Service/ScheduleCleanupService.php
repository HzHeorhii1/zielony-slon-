<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Schedule;

class ScheduleCleanupService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function cleanupUserSchedules(): void
    {
        $schedules = $this->entityManager->getRepository(Schedule::class)->findAll();

        foreach ($schedules as $schedule) {
            if($schedule->getUser()){
                $this->entityManager->remove($schedule);
            }

        }

        $this->entityManager->flush();
    }
}