<?php

namespace App\DataFixtures;


use App\Entity\User;
use App\Services\UserService;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends BaseFixture
{
    const INSTANCES_COUNT = 150;

    private UserService $userService;

    /**
     * UserFixtures constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        parent::__construct();

        $this->userService = $userService;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createMany(
            User::class,
            self::INSTANCES_COUNT,
            $manager,
            function (User $user, int $i) {
                $user->setName($this->faker->name);
                $user->setEmail($this->faker->unique()->email);
                $user->setEmailConfirmedAt(new DateTime());
                $user->setRoles([User::ROLE_APP_USER]);
                $user->setPlainPassword('secret123#');

                $this->userService->encodeUserPassword($user);
            }
        );

        $manager->flush();
    }
}
