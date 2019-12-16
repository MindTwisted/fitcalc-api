<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_translation",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="translation_lookup_unique_idx", columns={
 *         "locale", "product_id", "field"
 *     })}
 * )
 */
class ProductTranslation extends AbstractPersonalTranslation
{
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

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="translations")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;
}