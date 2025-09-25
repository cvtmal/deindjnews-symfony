<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\TwigFunction;

final class NewsletterPreviewController extends AbstractController
{
    #[Route('/newsletter/preview', name: 'app_newsletter_preview')]
    public function preview(): Response
    {
        // Create a mock tracking URL function
        $trackingUrl = (fn($url, $email = null): string =>
            // In production, this would add tracking parameters
            $url . '?utm_source=newsletter&utm_medium=email');

        // Sample data for preview
        $data = [
            'recipientName' => 'Max Mustermann',
            'recipientEmail' => 'max.mustermann@example.com',
            'unsubscribeUrl' => '#unsubscribe',
            'trackingUrl' => $trackingUrl,
        ];

        // Register the tracking URL function as a Twig runtime function
        $twig = $this->container->get('twig');
        $twig->addFunction(new TwigFunction('trackingUrl', $trackingUrl));

        return $this->render('emails/newsletter.html.twig', $data);
    }
}
