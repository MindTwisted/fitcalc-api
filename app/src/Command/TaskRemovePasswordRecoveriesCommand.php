<?php

namespace App\Command;


use App\Entity\PasswordRecovery;
use App\Repository\PasswordRecoveryRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskRemovePasswordRecoveriesCommand extends Command
{
    const AGE_ARGUMENT_NAME = 'password-recovery-age';

    protected static $defaultName = 'task:remove-password-recoveries';

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
    protected function configure()
    {
        $this
            ->setDescription('Remove password recoveries')
            ->addArgument(
                self::AGE_ARGUMENT_NAME,
                InputArgument::REQUIRED,
                'The number of hours since the creation of password recovery over which it will be deleted'
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
        $passwordRecoveryAge = filter_var($input->getArgument(self::AGE_ARGUMENT_NAME), FILTER_VALIDATE_INT);

        if (false === $passwordRecoveryAge) {
            throw new Exception(sprintf(
                'Invalid value has been provided for %s argument, value should be an integer.',
                self::AGE_ARGUMENT_NAME
            ));
        }

        /** @var PasswordRecoveryRepository $passwordRecoveryRepository */
        $passwordRecoveryRepository = $this->entityManager->getRepository(PasswordRecovery::class);
        $passwordRecoveries = $passwordRecoveryRepository->findOlderThan($passwordRecoveryAge);

        foreach ($passwordRecoveries as $passwordRecovery)
        {
            $this->entityManager->remove($passwordRecovery);
        }

        $this->entityManager->flush();

        $io->success("Password recoveries older than $passwordRecoveryAge hours were deleted.");

        return 0;
    }
}
