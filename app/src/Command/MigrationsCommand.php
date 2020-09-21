<?php

namespace App\Command;

use App\Repository\MigrationsRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationsCommand extends Command
{
    protected static $defaultName = 'app:migrate-database';

    protected MigrationsRepository $repo;

    public function __construct(MigrationsRepository $repo)
    {
        $this->repo = $repo;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Apply database migrations');

        $this->setHelp('This command applies all database migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = __DIR__ . '/../../migrations/';

        foreach (scandir($path) as $file) {
            if (strpos($file, '.sql') === false || $this->repo->isImported($file)) {
                continue;
            }

            $output->writeln('Processing ' . $file);

            $this->repo->importSqlFile($path . $file);
        }

        $output->writeln('Done.');

        return Command::SUCCESS;
    }
}
