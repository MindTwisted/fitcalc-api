<?php

namespace App\Entity;

use App\Repository\EatingSchemeDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EatingSchemeDetailRepository::class)
 */
class EatingSchemeDetail
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name;

    /**
     * @ORM\ManyToOne(targetEntity=EatingScheme::class, inversedBy="eatingSchemeDetails")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?EatingScheme $eatingScheme;

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

    public function getEatingScheme(): ?EatingScheme
    {
        return $this->eatingScheme;
    }

    public function setEatingScheme(?EatingScheme $eatingScheme): self
    {
        $this->eatingScheme = $eatingScheme;

        return $this;
    }
}
