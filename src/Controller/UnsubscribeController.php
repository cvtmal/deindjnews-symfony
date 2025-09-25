<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UnsubscribeController extends AbstractController
{
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {}

    #[Route('/unsubscribe/{email}', name: 'app_unsubscribe', methods: ['GET'])]
    public function unsubscribe(string $email): Response
    {
        $decodedEmail = base64_decode($email, true);

        if ($decodedEmail === false) {
            $this->logger->warning('Invalid encoded email for unsubscribe', ['encoded' => $email]);
            return $this->redirectToRoute('app_unsubscribe_success');
        }

        try {
            $subscriber = $this->subscriberRepository->findByEmail($decodedEmail);

            if ($subscriber && !$subscriber->isUnsubscribed()) {
                $subscriber->markAsUnsubscribed();
                $this->entityManager->flush();

                $this->logger->info('Subscriber unsubscribed', [
                    'email' => $decodedEmail,
                    'unsubscribed_at' => $subscriber->getUnsubscribedAt()->format('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $exception) {
            $this->logger->error('Error processing unsubscribe', [
                'email' => $decodedEmail,
                'error' => $exception->getMessage()
            ]);
        }

        return $this->redirectToRoute('app_unsubscribe_success');
    }

    #[Route('/unsubscribe-success', name: 'app_unsubscribe_success', methods: ['GET'])]
    public function success(): Response
    {
        return $this->render('unsubscribe/success.html.twig');
    }
}
