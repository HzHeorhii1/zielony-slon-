<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Subject")]
class Subject
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private ?int $subjectId = null;

    #[ORM\Column(type: "string")]
    private ?string $title = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $description = null;

    public function getSubjectId(): ?int
    {
        return $this->subjectId;
    }

    public function setSubjectId(int $subjectId): self
    {
        $this->subjectId = $subjectId;
        return $this;
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
}