<?php

namespace App\Entity;

use App\ORM\Mapping as ORM;

#[ORM\Table(name: "schedules")]
class Schedule
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    private ?string $title = null;

    #[ORM\Column(type: "text")]
    private ?string $description = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(type: "integer")]
    private ?int $worker_id = null;

    #[ORM\Column(type: "integer")]
    private ?int $room_id = null;

    #[ORM\Column(type: "integer")]
    private ?int $group_id = null;

    #[ORM\Column(type: "integer")]
    private ?int $subject_id = null;
    #[ORM\Column(type: "integer")]
    private ?int $user_id = null;

    #[ORM\Column(type: "string")]
    private ?string $lessonForm = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getWorkerId(): ?int
    {
        return $this->worker_id;
    }

    public function setWorkerId(?int $worker_id): self
    {
        $this->worker_id = $worker_id;
        return $this;
    }

    public function getRoomId(): ?int
    {
        return $this->room_id;
    }

    public function setRoomId(?int $room_id): self
    {
        $this->room_id = $room_id;
        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->group_id;
    }

    public function setGroupId(?int $group_id): self
    {
        $this->group_id = $group_id;
        return $this;
    }

    public function getSubjectId(): ?int
    {
        return $this->subject_id;
    }
    public function setSubjectId(?int $subject_id): self
    {
        $this->subject_id = $subject_id;
        return $this;
    }
    public function getUserId(): ?int
    {
        return $this->user_id;
    }
    public function setUserId(?int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getLessonForm(): ?string
    {
        return $this->lessonForm;
    }

    public function setLessonForm(?string $lessonForm): self
    {
        $this->lessonForm = $lessonForm;
        return $this;
    }
}