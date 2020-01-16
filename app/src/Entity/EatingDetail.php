<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EatingDetailRepository")
 */
class EatingDetail
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Eating", inversedBy="eatingDetails")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eating;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private $weight;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Eating|null
     */
    public function getEating(): ?Eating
    {
        return $this->eating;
    }

    /**
     * @param Eating|null $eating
     *
     * @return $this
     */
    public function setEating(?Eating $eating): self
    {
        $this->eating = $eating;

        return $this;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product|null $product
     *
     * @return $this
     */
    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     *
     * @return $this
     */
    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }
}
