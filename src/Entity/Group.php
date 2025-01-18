<?php

namespace App\Entity;

use App\ORM\Mapping as ORM;

#[ORM\Table(name: "study_groups")]
class Group
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}