<?php

namespace App\DataFixtures;


use App\Entity\Eating;
use App\Entity\EatingDetail;
use App\Entity\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EatingDetailFixtures extends BaseFixture implements DependentFixtureInterface
{
    const INSTANCES_COUNT = 2500;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createMany(
            EatingDetail::class,
            self::INSTANCES_COUNT,
            $manager,
            function (EatingDetail $eatingDetail, int $i) {
                /** @var Eating $eating */
                $eating = $this->getReference(Eating::class . '_' . $this->faker->biasedNumberBetween(0, EatingFixtures::INSTANCES_COUNT - 1));

                /** @var Product $product */
                $product = $this->getReference(Product::class . '_' . $this->faker->biasedNumberBetween(0, ProductFixtures::INSTANCES_COUNT - 1));

                $eatingDetail->setEating($eating);
                $eatingDetail->setProduct($product);
                $eatingDetail->setWeight($this->faker->biasedNumberBetween(25, 500));
            }
        );

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            EatingFixtures::class,
            ProductFixtures::class
        ];
    }
}
