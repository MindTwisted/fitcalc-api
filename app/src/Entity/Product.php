<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @UniqueEntity("name")
 * @Gedmo\TranslationEntity(class="App\Entity\ProductTranslation")
 */
class Product implements Translatable
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
     */
    private $user;

    /**
     * @Gedmo\Translatable
     *
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="5")
     *
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="100")
     */
    private $proteins;

    /**
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="100")
     */
    private $fats;

    /**
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="100")
     */
    private $carbohydrates;

    /**
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="1000")
     */
    private $calories;

    /**
     * @ORM\OneToMany(
     *   targetEntity="ProductTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     *
     * @Assert\Valid()
     */
    private $translations;

    /**
     * @Gedmo\Locale
     *
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
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
     * @return float|null
     */
    public function getProteins(): ?float
    {
        return $this->proteins;
    }

    /**
     * @param float $proteins
     *
     * @return $this
     */
    public function setProteins(float $proteins): self
    {
        $this->proteins = $proteins;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getFats(): ?float
    {
        return $this->fats;
    }

    /**
     * @param float $fats
     *
     * @return $this
     */
    public function setFats(float $fats): self
    {
        $this->fats = $fats;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCarbohydrates(): ?float
    {
        return $this->carbohydrates;
    }

    /**
     * @param float $carbohydrates
     *
     * @return $this
     */
    public function setCarbohydrates(float $carbohydrates): self
    {
        $this->carbohydrates = $carbohydrates;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCalories(): ?int
    {
        return $this->calories;
    }

    /**
     * @param int $calories
     *
     * @return $this
     */
    public function setCalories(int $calories): self
    {
        $this->calories = $calories;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param ProductTranslation $productTranslation
     */
    public function addTranslation(ProductTranslation $productTranslation)
    {
        if (!$this->translations->contains($productTranslation)) {
            $this->translations[] = $productTranslation;

            $productTranslation->setObject($this);
        }
    }

    /**
     * @param string $locale
     *
     * @return Product
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }
}
