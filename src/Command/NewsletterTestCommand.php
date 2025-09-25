<?php

namespace App\Command;

use App\Service\NewsletterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'newsletter:test',
    description: 'Send a test newsletter to a specific email address',
)]
class NewsletterTestCommand extends Command
{
    public function __construct(
        private NewsletterService $newsletterService,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email address to send test newsletter to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $errors = $this->validator->validate($email, [new Email()]);
        if (count($errors) > 0) {
            $io->error('Invalid email address provided: ' . $email);
            return Command::FAILURE;
        }

        $io->info(sprintf('Sending test newsletter to: %s', $email));

        try {
            $this->newsletterService->sendTestNewsletter($email);

            $io->success(sprintf('Test newsletter successfully sent to: %s', $email));
            $this->logger->info('Test newsletter sent', ['email' => $email]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $errorMessage = sprintf(
                'Failed to send test newsletter to %s: %s',
                $email,
                $e->getMessage()
            );

            $io->error($errorMessage);
            $this->logger->error($errorMessage, [
                'email' => $email,
                'exception' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }
}