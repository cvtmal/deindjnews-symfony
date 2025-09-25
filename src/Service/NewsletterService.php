<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class NewsletterService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function sendNewsletter(string $recipientEmail, ?string $recipientName = null): void
    {
        $encodedEmail = base64_encode($recipientEmail);

        $html = $this->twig->render('emails/newsletter.html.twig', [
            'recipientName' => $recipientName,
            'recipientEmail' => $recipientEmail,
            'encodedEmail' => $encodedEmail,
            'trackingUrl' => $this->generateTrackingUrl(),
            'unsubscribeUrl' => $this->urlGenerator->generate(
                'app_unsubscribe',
                ['email' => $encodedEmail],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        $email = (new Email())
            ->from('hallo@deindj.ch')
            ->to($recipientEmail)
            ->subject('Neu bei DeinDJ.ch: Mehr Musik für euren Anlass')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendTestNewsletter(string $recipientEmail): void
    {
        $this->sendNewsletter($recipientEmail, 'Test Subscriber');
    }

    private function generateTrackingUrl(): callable
    {
        return function (string $url, string $email) {
            return $this->urlGenerator->generate(
                'app_click_tracking',
                [
                    'url' => base64_encode($url),
                    'email' => base64_encode($email),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        };
    }
}