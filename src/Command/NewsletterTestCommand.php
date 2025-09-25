<?php

declare(strict_types=1);

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
        private readonly NewsletterService $newsletterService,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
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
        $symfonyStyle = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $constraintViolationList = $this->validator->validate($email, [new Email()]);
        if (count($constraintViolationList) > 0) {
            $symfonyStyle->error('Invalid email address provided: ' . $email);
            return Command::FAILURE;
        }

        $symfonyStyle->info(sprintf('Sending test newsletter to: %s', $email));

        try {
            $this->newsletterService->sendTestNewsletter($email);

            $symfonyStyle->success(sprintf('Test newsletter successfully sent to: %s', $email));
            $this->logger->info('Test newsletter sent', ['email' => $email]);

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $errorMessage = sprintf(
                'Failed to send test newsletter to %s: %s',
                $email,
                $exception->getMessage()
            );

            $symfonyStyle->error($errorMessage);
            $this->logger->error($errorMessage, [
                'email' => $email,
                'exception' => $exception->getMessage()
            ]);

            return Command::FAILURE;
        }
    }
}
