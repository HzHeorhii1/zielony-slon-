<?php

namespace App\Entity;

// no doctrine
use App\ORM\Mapping as ORM;

#[ORM\Table(name: "workers")]
class Worker
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