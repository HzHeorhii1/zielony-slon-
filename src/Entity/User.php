<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "User")]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private ?int $userld = null;

    #[ORM\Column(type: "integer")]
    private ?int $nrAlbumu = null;

    public function getUserld(): ?int
    {
        return $this->userld;
    }

    public function setUserld(int $userld): self
    {
        $this->userld = $userld;
        return $this;
    }

    public function getNrAlbumu(): ?int
    {
        return $this->nrAlbumu;
    }

    public function setNrAlbumu(int $nrAlbumu): self
    {
        $this->nrAlbumu = $nrAlbumu;
        return $this;
    }
}