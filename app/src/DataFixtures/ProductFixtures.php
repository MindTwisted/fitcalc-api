<?php

namespace App\DataFixtures;


use App\Entity\Product;
use App\Entity\ProductTranslation;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixtures extends BaseFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->createMany(
            Product::class,
            150,
            $manager,
            function (Product $product, int $i) {
                $proteins = $this->faker->numberBetween(0, 25);
                $fats = $this->faker->numberBetween(0, 25);
                $carbohydrates = $this->faker->numberBetween(0, 50);
                $calories = ($proteins * 4) + ($fats * 10) + ($carbohydrates * 4);

                $product->setName($this->faker->unique()->words(3, true));
                $product->setProteins($proteins);
                $product->setFats($fats);
                $product->setCarbohydrates($carbohydrates);
                $product->setCalories($calories);
                $product->setLocale('en');
                $product->addTranslation(
                    new ProductTranslation(
                        'ru',
                        'name',
                        $this->russianFaker->unique()->realText(30)
                    )
                );
            }
        );

        $manager->flush();
    }
}
