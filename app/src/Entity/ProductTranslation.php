<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_translation",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="translation_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 * @UniqueEntity("content")
 */
class ProductTranslation extends AbstractPersonalTranslation
{
    /**
     * @var string $content
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="5")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;

    /**
     * CategoryTranslation constructor
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}