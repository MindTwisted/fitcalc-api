<?php

namespace App\DataFixtures;


use App\Entity\Eating;
use App\Entity\User;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EatingFixtures extends BaseFixture implements DependentFixtureInterface
{
    const INSTANCES_COUNT = 5000;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createMany(
            Eating::class,
            self::INSTANCES_COUNT,
            $manager,
            function (Eating $eating, int $i) {
                /** @var User $user */
                $user = $this->getReference(User::class . '_' . $this->faker->biasedNumberBetween(0, UserFixtures::INSTANCES_COUNT - 1));

                $eating->setName($this->faker->words(2, true));
                $eating->setUser($user);
                $eating->setOccurredAt($this->faker->dateTimeBetween('-1 month', 'now'));
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
            UserFixtures::class
        ];
    }
}
