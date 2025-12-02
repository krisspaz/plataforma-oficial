<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contracts')]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contracts')]
    private ?ParentEntity $parent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?ParentEntity
    {
        return $this->parent;
    }

    public function setParent(?ParentEntity $parent): static
    {
        $this->parent = $parent;
        return $this;
    }
}
