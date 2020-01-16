<?php

namespace App\Entity;


use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EatingRepository")
 */
class Eating
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $occurredAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EatingDetail", mappedBy="eating", orphanRemoval=true)
     */
    private $eatingDetails;

    public function __construct()
    {
        $this->eatingDetails = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getOccurredAt(): ?DateTimeInterface
    {
        return $this->occurredAt;
    }

    /**
     * @param DateTimeInterface $occurredAt
     *
     * @return $this
     */
    public function setOccurredAt(DateTimeInterface $occurredAt): self
    {
        $this->occurredAt = $occurredAt;

        return $this;
    }

    /**
     * @return Collection|EatingDetail[]
     */
    public function getEatingDetails(): Collection
    {
        return $this->eatingDetails;
    }

    public function addEatingDetail(EatingDetail $eatingDetail): self
    {
        if (!$this->eatingDetails->contains($eatingDetail)) {
            $this->eatingDetails[] = $eatingDetail;
            $eatingDetail->setEating($this);
        }

        return $this;
    }

    public function removeEatingDetail(EatingDetail $eatingDetail): self
    {
        if ($this->eatingDetails->contains($eatingDetail)) {
            $this->eatingDetails->removeElement($eatingDetail);
            // set the owning side to null (unless already changed)
            if ($eatingDetail->getEating() === $this) {
                $eatingDetail->setEating(null);
            }
        }

        return $this;
    }
}
