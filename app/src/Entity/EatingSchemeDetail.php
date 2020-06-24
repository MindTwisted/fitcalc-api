<?php

namespace App\Entity;

use App\Repository\EatingSchemeDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EatingSchemeDetailRepository::class)
 * @ORM\Table(
 *     name="eating_scheme_detail",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="eating_scheme_name", columns={"eating_scheme_id", "name"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"name", "eatingScheme"},
 *     repositoryMethod="findOneByNameAndEatingScheme"
 * )
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
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="3")
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
