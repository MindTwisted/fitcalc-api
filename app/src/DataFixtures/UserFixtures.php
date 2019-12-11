<?php

namespace App\DataFixtures;


use App\Entity\Email;
use App\Entity\User;
use App\Services\UserService;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends BaseFixture
{
    /** @var UserService */
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
            50,
            $manager,
            function (User $user, int $i) {
                $email = new Email();
                $email->setEmail($this->faker->unique()->email);
                $email->setVerified(true);

                $user->setFullname($this->faker->name);
                $user->setRoles([User::ROLE_APP_USER]);
                $user->setUsername($this->faker->uuid);
                $user->setPassword($this->faker->word);
                $user->addEmail($email);

                $this->userService->encodeUserPassword($user);
            }
        );

        $manager->flush();
    }
}
