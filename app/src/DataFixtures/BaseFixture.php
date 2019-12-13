<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
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
     * BaseFixture constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @param string $className
     * @param int $count
     * @param ObjectManager $manager
     * @param callable $factory
     */
    protected function createMany(string $className, int $count, ObjectManager $manager, callable $factory)
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