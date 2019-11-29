<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GenerateKeysCommand extends Command
{
    protected static $defaultName = 'generate:keys';

    /** @var Filesystem */
    private $fileSystem;

    /**
     * GenerateKeysCommand constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->fileSystem = $filesystem;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate public and private keys pair.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $passphrase = $_ENV['KEYS_PASSPHRASE'];
        $keysPath = $_ENV['APP_ROOT'] . $_ENV['JWT_KEYS_PATH'];
        $privateKey = $keysPath . '/' . $_ENV['JWT_PRIVATE_KEY_NAME'];
        $publicKey = $keysPath . '/' . $_ENV['JWT_PUBLIC_KEY_NAME'];

        if (!$this->fileSystem->exists($keysPath)) {
            $this->fileSystem->mkdir($keysPath, 0777);
        }

        $process = Process::fromShellCommandline("openssl genpkey -aes-256-cbc -pass pass:$passphrase -algorithm RSA -out $privateKey -pkeyopt rsa_keygen_bits:4096");
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $process = Process::fromShellCommandline("openssl rsa -pubout -in $privateKey -out $publicKey -passin pass:$passphrase");
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $io->success('Keys have been successfully generated.');

        return 0;
    }
}
