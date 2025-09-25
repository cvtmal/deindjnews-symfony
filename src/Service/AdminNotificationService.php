<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class AdminNotificationService
{
    /** @var array<int, string> */
    private readonly array $adminEmails;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        string $adminEmails
    ) {
        $this->adminEmails = array_map('trim', explode(',', $adminEmails));
    }

    public function sendFailureNotification(string $subscriberEmail, string $errorMessage): void
    {
        $timestamp = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Zurich'));

        foreach ($this->adminEmails as $adminEmail) {
            try {
                $email = (new Email())
                    ->from('noreply@deindj.ch')
                    ->to($adminEmail)
                    ->subject('Newsletter Delivery Failed')
                    ->html($this->createFailureEmailHtml($subscriberEmail, $errorMessage, $timestamp))
                    ->text($this->createFailureEmailText($subscriberEmail, $errorMessage, $timestamp));

                $this->mailer->send($email);
                $this->logger->info('Admin notification sent', ['admin' => $adminEmail, 'subscriber' => $subscriberEmail]);
            } catch (\Exception $e) {
                $this->logger->error('Failed to send admin notification', [
                    'admin' => $adminEmail,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function createFailureEmailHtml(string $subscriberEmail, string $errorMessage, \DateTimeInterface $timestamp): string
    {
        return sprintf(
            '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #dc2626;
            margin-bottom: 20px;
        }
        .details {
            background-color: #f8f9fa;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
        }
        .detail-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Newsletter Delivery Failed</h1>
        <p>The newsletter delivery system encountered an error when attempting to send to a subscriber.</p>

        <div class="details">
            <div class="detail-row">
                <span class="label">Subscriber Email:</span> %s
            </div>
            <div class="detail-row">
                <span class="label">Timestamp:</span> %s
            </div>
            <div class="detail-row">
                <span class="label">Error Message:</span>
                <pre style="margin-top: 10px; white-space: pre-wrap;">%s</pre>
            </div>
        </div>

        <p style="margin-top: 30px; color: #6b7280;">
            This is an automated notification from the DeinDJ Newsletter System.
        </p>
    </div>
</body>
</html>',
            htmlspecialchars($subscriberEmail),
            $timestamp->format('Y-m-d H:i:s T'),
            htmlspecialchars($errorMessage)
        );
    }

    private function createFailureEmailText(string $subscriberEmail, string $errorMessage, \DateTimeInterface $timestamp): string
    {
        return sprintf(
            "Newsletter Delivery Failed\n" .
            "==========================\n\n" .
            "The newsletter delivery system encountered an error when attempting to send to a subscriber.\n\n" .
            "Details:\n" .
            "--------\n" .
            "Subscriber Email: %s\n" .
            "Timestamp: %s\n" .
            "Error Message:\n%s\n\n" .
            "This is an automated notification from the DeinDJ Newsletter System.",
            $subscriberEmail,
            $timestamp->format('Y-m-d H:i:s T'),
            $errorMessage
        );
    }
}
