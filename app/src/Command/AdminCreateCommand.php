<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\UserService;
use App\Services\ValidationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AdminCreateCommand extends Command
{
    protected static $defaultName = 'admin:create';

    /**
     * @var ValidationService
     */
    private $validationService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * AdminCreateCommand constructor.
     *
     * @param ValidationService $validationService
     * @param UserService $userService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ValidationService $validationService,
        UserService $userService,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();

        $this->validationService = $validationService;
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create admin for application')
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $adminUsers = $userRepository->findAdminUsers();

        if (count($adminUsers) > 0) {
            throw new Exception('Admin user already exists.');
        }

        $user = new User();
        $user->setName('Admin');
        $user->setEmail($input->getArgument('email'));
        $user->setEmailConfirmedAt(new DateTime());
        $user->setPlainPassword($input->getArgument('password'));
        $user->setRoles([User::ROLE_ADMIN]);

        $this->validationService->validate($user);
        $this->userService->encodeUserPassword($user);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Admin has been created.');

        return 0;
    }
}
