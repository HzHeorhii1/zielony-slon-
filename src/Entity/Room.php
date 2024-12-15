<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Room")]
class Room
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private ?int $roomld = null;

    #[ORM\Column(type: "string")]
    private ?string $name = null;

    public function getRoomld(): ?int
    {
        return $this->roomld;
    }

    public function setRoomld(int $roomld): self
    {
        $this->roomld = $roomld;
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
}