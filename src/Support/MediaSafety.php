<?php

namespace DefStudio\Whatsapper\Support;

use DefStudio\Whatsapper\Exceptions\WhatsapperMediaException;

final class MediaSafety
{
    public static function validateId(string $id): void
    {
        if (! preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9._-]{0,199}\z/', $id)) {
            throw WhatsapperMediaException::invalidId();
        }
    }

    public static function validateDownloadUrl(string $url): void
    {
        $parts = parse_url($url);

        if ($parts === false
            || ($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || (isset($parts['port']) && $parts['port'] !== 443)
            || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw WhatsapperMediaException::invalidDownloadUrl();
        }

        $host = strtolower($parts['host'] ?? '');

        foreach (['facebook.com', 'fbsbx.com', 'fbcdn.net'] as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return;
            }
        }

        throw WhatsapperMediaException::invalidDownloadUrl();
    }
}
