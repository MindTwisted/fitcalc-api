<?php

namespace App\Entity;


use App\Repository\EatingSchemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EatingSchemeRepository::class)
 * @ORM\Table(
 *     name="eating_scheme",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="user_name", columns={"user_id", "name"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"name", "user"},
 *     repositoryMethod="findOneByNameAndUser"
 * )
 */
class EatingScheme
{
    use TimestampableEntity;

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
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isDefault = false;

    /**
     * @ORM\OneToMany(targetEntity=EatingSchemeDetail::class, mappedBy="eating_scheme", orphanRemoval=true)
     */
    private Collection $eatingSchemeDetails;

    public function __construct()
    {
        $this->eatingSchemeDetails = new ArrayCollection();
    }

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return Collection|EatingSchemeDetail[]
     */
    public function getEatingSchemeDetails(): Collection
    {
        return $this->eatingSchemeDetails;
    }

    public function addEatingSchemeDetail(EatingSchemeDetail $eatingSchemeDetail): self
    {
        if (!$this->eatingSchemeDetails->contains($eatingSchemeDetail)) {
            $this->eatingSchemeDetails[] = $eatingSchemeDetail;
            $eatingSchemeDetail->setEatingScheme($this);
        }

        return $this;
    }

    public function removeEatingSchemeDetail(EatingSchemeDetail $eatingSchemeDetail): self
    {
        if ($this->eatingSchemeDetails->contains($eatingSchemeDetail)) {
            $this->eatingSchemeDetails->removeElement($eatingSchemeDetail);
            // set the owning side to null (unless already changed)
            if ($eatingSchemeDetail->getEatingScheme() === $this) {
                $eatingSchemeDetail->setEatingScheme(null);
            }
        }

        return $this;
    }
}
