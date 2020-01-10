<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskRemoveUsersWithNotConfirmedEmailsCommand extends Command
{
    const AGE_ARGUMENT_NAME = 'account-age';

    protected static $defaultName = 'task:remove-users-with-not-confirmed-emails';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * TaskRemoveUsersWithNotConfirmedEmailsCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Remove users with not confirmed emails')
            ->addArgument(
                self::AGE_ARGUMENT_NAME,
                InputArgument::REQUIRED,
                'The number of hours since the creation of the user account over which the account will be deleted'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $accountAge = filter_var($input->getArgument(self::AGE_ARGUMENT_NAME), FILTER_VALIDATE_INT);

        if (false === $accountAge) {
            throw new Exception(sprintf(
                'Invalid value has been provided for %s argument, value should be an integer.',
                self::AGE_ARGUMENT_NAME
            ));
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findAppUsersWithNotConfirmedEmailsOlderThan($accountAge);

        foreach ($users as $user)
        {
            $this->entityManager->remove($user);
        }

        $this->entityManager->flush();

        $io->success("Users with not confirmed emails older than $accountAge hours were deleted.");

        return 0;
    }
}
