<?php

namespace App\Command;


use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskRemoveExpiredAndSoftDeletedRefreshTokensCommand extends Command
{
    const AGE_ARGUMENT_NAME = 'token-age';

    protected static $defaultName = 'task:remove-expired-and-soft-deleted-refresh-tokens';

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
            ->setDescription('Remove expired and soft deleted refresh tokens')
            ->addArgument(
                self::AGE_ARGUMENT_NAME,
                InputArgument::REQUIRED,
                'The number of hours since the creation of refresh token over which the token will be deleted'
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
        $tokenAge = filter_var($input->getArgument(self::AGE_ARGUMENT_NAME), FILTER_VALIDATE_INT);

        if (false === $tokenAge) {
            throw new Exception(sprintf(
                'Invalid value has been provided for %s argument, value should be an integer.',
                self::AGE_ARGUMENT_NAME
            ));
        }

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->entityManager->getRepository(RefreshToken::class);
        $refreshTokens = $refreshTokenRepository->findExpiredOrDeletedOlderThan($tokenAge);

        foreach ($refreshTokens as $refreshToken)
        {
            $refreshToken->setDeletedAt(new DateTime());
            $this->entityManager->remove($refreshToken);
        }

        $this->entityManager->flush();

        $io->success("Soft deleted and expired refresh tokens older than $tokenAge hours were deleted.");

        return 0;
    }
}
