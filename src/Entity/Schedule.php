<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "schedules")]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    private ?string $title = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Worker")]
    #[ORM\JoinColumn(name: "worker_id", referencedColumnName: "id", nullable: true)]
    private ?Worker $worker = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Room")]
    #[ORM\JoinColumn(name: "room_id", referencedColumnName: "id", nullable: true)]
    private ?Room $room = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Group")]
    #[ORM\JoinColumn(name: "group_id", referencedColumnName: "id", nullable: true)]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Subject")]
    #[ORM\JoinColumn(name: "subject_id", referencedColumnName: "id", nullable: true)]
    private ?Subject $subject = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "userld", nullable: true)]
    private ?User $user = null;

    #[ORM\Column(type: "string", nullable: true)]
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

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;
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
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;
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