<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FpmStatusCommand extends Command
{
    protected static $defaultName = 'app:fpm-status';

    protected function configure(): void
    {
        $this->setDescription('Show PHP-FPM status');
        $this->setHelp('This command shows the PHP-FPM status');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $phpSocket = fsockopen('php', 9000);

        // fcgi GET /status HTTP/1.1
        $packet = '"\u0001\u0001\u0000\u0000\u0000\b\u0000\u0000\u0000\u0001\u0000\u0000\u0000\u0000\u0000\u0000\u0001\u0004' .
            '\u0000\u0000\u0000?\u0001\u0000\u000f\u0007SCRIPT_FILENAME\/status\u000b\u0007SCRIPT_NAME\/status\u000e\u0003' .
            'REQUEST_METHODGET\u0000\u0001\u0004\u0000\u0000\u0000\u0000\u0000\u0000\u0001\u0005\u0000\u0000\u0000\u0000\u0000\u0000"';

        fwrite($phpSocket, json_decode($packet));
        $output->writeln(fread($phpSocket, 4096));
        fclose($phpSocket);

        return Command::SUCCESS;
    }
}
