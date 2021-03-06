<?php

namespace App\Command;

use App\Model\Customer;
use App\Service\JwtManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TokenGenerationCommand extends Command
{
    protected static $defaultName = 'app:generate-token';

    protected JwtManager $jwtManager;

    public function __construct(JwtManager $jwtManager)
    {
        $this->jwtManager = $jwtManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generate JWT customer token');
        $this->setHelp('This command generates a JWT customer token');

        $this->addArgument('customerid', InputArgument::REQUIRED, 'Customer ID');
        $this->addArgument('email', InputArgument::REQUIRED, 'Customer Email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = $input->getArguments();

        $customer = new Customer();
        $customer->id = (int) ($arguments['customerid'] ?? 0);
        $customer->email = (string) ($arguments['email'] ?? '');

        $token = $this->jwtManager->getToken($customer);

        $output->writeln('export TOKEN="' . $token . '"');

        return Command::SUCCESS;
    }
}
