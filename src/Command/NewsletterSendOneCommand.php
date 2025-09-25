<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\SubscriberRepository;
use App\Service\AdminNotificationService;
use App\Service\NewsletterService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'newsletter:send-one',
    description: 'Send newsletter to one unsent subscriber',
)]
class NewsletterSendOneCommand extends Command
{
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository,
        private readonly NewsletterService $newsletterService,
        private readonly AdminNotificationService $adminNotificationService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $subscriber = $this->subscriberRepository->findNextUnsentSubscriber();

        if (!$subscriber instanceof \App\Entity\Subscriber) {
            $symfonyStyle->info('No unsent subscribers found.');
            $this->logger->info('No unsent subscribers found');
            return Command::SUCCESS;
        }

        $symfonyStyle->info(sprintf('Sending newsletter to: %s', $subscriber->getEmail()));

        try {
            $this->newsletterService->sendNewsletter(
                $subscriber->getEmail(),
                $subscriber->getName()
            );

            $subscriber->markAsSent();
            $this->entityManager->flush();

            $symfonyStyle->success(sprintf('Newsletter successfully sent to: %s', $subscriber->getEmail()));
            $this->logger->info('Newsletter sent successfully', [
                'email' => $subscriber->getEmail(),
                'sent_at' => $subscriber->getSentAt()->format('Y-m-d H:i:s')
            ]);

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $errorMessage = sprintf(
                'Failed to send newsletter to %s: %s',
                $subscriber->getEmail(),
                $exception->getMessage()
            );

            $symfonyStyle->error($errorMessage);
            $this->logger->error($errorMessage, [
                'email' => $subscriber->getEmail(),
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            try {
                $this->adminNotificationService->sendFailureNotification(
                    $subscriber->getEmail(),
                    $exception->getMessage()
                );
            } catch (\Exception $notificationException) {
                $this->logger->error('Failed to send admin notification', [
                    'exception' => $notificationException->getMessage()
                ]);
            }

            return Command::FAILURE;
        }
    }
}
