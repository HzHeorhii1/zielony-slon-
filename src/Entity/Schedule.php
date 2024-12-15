<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Schedule")]
class Schedule
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private ?int $scheduleld = null;

    #[ORM\Column(type: "string")]
    private ?string $startTime = null;

    #[ORM\Column(type: "string")]
    private ?string $endTime = null;

    #[ORM\Column(type: "string")]
    private ?string $lessonForm = null;

    #[ORM\Column(type: "string")]
    private ?string $status = null;

    #[ORM\Column(type: "integer")]
    private ?int $hours = null;

    #[ORM\Column(type: "string")]
    private ?string $color = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Subject", cascade: ["remove"])]
    #[ORM\JoinColumn(name: "subjectId", referencedColumnName: "subjectId", onDelete: "CASCADE")]
    private ?Subject $subject = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Group", cascade: ["remove"])]
    #[ORM\JoinColumn(name: "groupld", referencedColumnName: "groupld", onDelete: "CASCADE")]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Worker", cascade: ["remove"])]
    #[ORM\JoinColumn(name: "workerld", referencedColumnName: "workerld", onDelete: "CASCADE")]
    private ?Worker $worker = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Room", cascade: ["remove"])]
    #[ORM\JoinColumn(name: "roomld", referencedColumnName: "roomld", onDelete: "CASCADE")]
    private ?Room $room = null;

    #[ORM\Column(type: "string")]
    private ?string $lessonFormShort = null;

    #[ORM\Column(type: "string")]
    private ?string $lessonStatusShort = null;

    #[ORM\Column(type: "string")]
    private ?string $borderColor = null;

    public function getScheduleld(): ?int
    {
        return $this->scheduleld;
    }

    public function setScheduleld(int $scheduleld): self
    {
        $this->scheduleld = $scheduleld;
        return $this;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getLessonForm(): ?string
    {
        return $this->lessonForm;
    }

    public function setLessonForm(string $lessonForm): self
    {
        $this->lessonForm = $lessonForm;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getHours(): ?int
    {
        return $this->hours;
    }

    public function setHours(int $hours): self
    {
        $this->hours = $hours;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getWorker(): ?Worker
    {
        return $this->worker;
    }

    public function setWorker(?Worker $worker): self
    {
        $this->worker = $worker;
        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;
        return $this;
    }

    public function getLessonFormShort(): ?string
    {
        return $this->lessonFormShort;
    }

    public function setLessonFormShort(string $lessonFormShort): self
    {
        $this->lessonFormShort = $lessonFormShort;
        return $this;
    }

    public function getLessonStatusShort(): ?string
    {
        return $this->lessonStatusShort;
    }

    public function setLessonStatusShort(string $lessonStatusShort): self
    {
        $this->lessonStatusShort = $lessonStatusShort;
        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(string $borderColor): self
    {
        $this->borderColor = $borderColor;
        return $this;
    }
}