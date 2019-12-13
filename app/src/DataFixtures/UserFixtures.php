<?php

namespace App\DataFixtures;


use App\Entity\User;
use App\Services\UserService;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends BaseFixture
{
    /**
     * @var UserService
     */
    private $userService;

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
    public function load(ObjectManager $manager)
    {
        $this->createMany(
            User::class,
            150,
            $manager,
            function (User $user, int $i) {
                $user->setName($this->faker->name);
                $user->setEmail($this->faker->unique()->email);
                $user->setEmailConfirmedAt(new DateTime());
                $user->setRoles([User::ROLE_APP_USER]);
                $user->setPlainPassword($this->faker->word);

                $this->userService->encodeUserPassword($user);
            }
        );

        $manager->flush();
    }
}
