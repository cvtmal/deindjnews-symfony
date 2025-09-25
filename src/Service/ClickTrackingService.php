<?php

namespace App\Service;

class ClickTrackingService
{
    private const LINK_NAME_MAPPING = [
        '/saxophonisten' => 'Saxophonisten',
        '/partybands' => 'Partybands',
        '/saenger' => 'Sänger',
        '/traurednerinnen' => 'Trauredner',
        '/hochzeitsfotografen' => 'Fotografen',
        '/djs' => 'DJs',
    ];

    public function extractLinkName(string $url): string
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        if (isset(self::LINK_NAME_MAPPING[$path])) {
            return self::LINK_NAME_MAPPING[$path];
        }

        if ($path === '/' || $path === '') {
            return 'Homepage';
        }

        return 'Jetzt anfragen';
    }

    public function isValidDeinDjUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);
        if ($parsedUrl === false) {
            return false;
        }

        $host = $parsedUrl['host'] ?? '';

        return $host === 'deindj.ch' ||
               $host === 'www.deindj.ch' ||
               str_ends_with($host, '.deindj.ch');
    }

    public function decodeUrl(string $encodedUrl): ?string
    {
        $decoded = base64_decode($encodedUrl, true);
        return $decoded !== false ? $decoded : null;
    }

    public function decodeEmail(string $encodedEmail): ?string
    {
        $decoded = base64_decode($encodedEmail, true);
        return $decoded !== false ? $decoded : null;
    }
}