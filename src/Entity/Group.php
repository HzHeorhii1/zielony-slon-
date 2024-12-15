<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "`Group`")]
class Group
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private ?int $groupld = null;

    #[ORM\Column(type: "string")]
    private ?string $name = null;

    #[ORM\Column(type: "string")]
    private ?string $tokName = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User", cascade: ["remove"])] // Додаємо каскад
    #[ORM\JoinColumn(name: "userld", referencedColumnName: "userld", onDelete: "CASCADE")]
    private ?User $user = null;

    public function getGroupld(): ?int
    {
        return $this->groupld;
    }

    public function setGroupld(int $groupld): self
    {
        $this->groupld = $groupld;
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

    public function getTokName(): ?string
    {
        return $this->tokName;
    }

    public function setTokName(string $tokName): self
    {
        $this->tokName = $tokName;
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
}