<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * Class BaseFixture
 *
 * @package App\DataFixtures
 */
abstract class BaseFixture extends Fixture
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @var Generator
     */
    protected $russianFaker;

    /**
     * BaseFixture constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
        $this->russianFaker = Factory::create('ru_RU');
    }

    /**
     * @param string $className
     * @param int $count
     * @param ObjectManager $manager
     * @param callable $factory
     */
    protected function createMany(string $className, int $count, ObjectManager $manager, callable $factory): void
    {
        for ($i = 0; $i < $count; $i++) {
            $entity = new $className();

            $factory($entity, $i);

            $manager->persist($entity);

            // store for usage later as App\Entity\ClassName_#COUNT#
            $this->addReference($className . '_' . $i, $entity);
        }
    }
}