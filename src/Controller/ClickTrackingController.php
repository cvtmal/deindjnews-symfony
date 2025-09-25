<?php

namespace App\Controller;

use App\Repository\SubscriberRepository;
use App\Service\ClickTrackingService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ClickTrackingController extends AbstractController
{
    public function __construct(
        private ClickTrackingService $clickTrackingService,
        private SubscriberRepository $subscriberRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    #[Route('/click', name: 'app_click_tracking', methods: ['GET'])]
    public function track(Request $request): Response
    {
        $encodedUrl = $request->query->get('url', '');
        $encodedEmail = $request->query->get('email', '');

        $url = $this->clickTrackingService->decodeUrl($encodedUrl);
        $email = $this->clickTrackingService->decodeEmail($encodedEmail);

        if (!$url || !$email) {
            $this->logger->warning('Invalid tracking parameters', [
                'encoded_url' => $encodedUrl,
                'encoded_email' => $encodedEmail
            ]);
            return new RedirectResponse('https://deindj.ch');
        }

        if (!$this->clickTrackingService->isValidDeinDjUrl($url)) {
            $this->logger->warning('Invalid redirect URL attempted', [
                'url' => $url,
                'email' => $email
            ]);
            return new RedirectResponse('https://deindj.ch');
        }

        try {
            $subscriber = $this->subscriberRepository->findByEmail($email);
            if ($subscriber) {
                $linkName = $this->clickTrackingService->extractLinkName($url);
                $subscriber->recordClick($linkName);
                $this->entityManager->flush();

                $this->logger->info('Click tracked successfully', [
                    'email' => $email,
                    'url' => $url,
                    'link_name' => $linkName
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error tracking click', [
                'email' => $email,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }

        return new RedirectResponse($url);
    }
}