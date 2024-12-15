<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "Worker")]
class Worker
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private ?int $workerld = null;

    #[ORM\Column(type: "string")]
    private ?string $name = null;

    #[ORM\Column(type: "string")]
    private ?string $title = null;

    public function getWorkerld(): ?int
    {
        return $this->workerld;
    }

    public function setWorkerld(int $workerld): self
    {
        $this->workerld = $workerld;
        return $this;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
}