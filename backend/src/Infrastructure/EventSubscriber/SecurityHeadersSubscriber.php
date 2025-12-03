<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        // Strict Transport Security (HSTS)
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        // Content Security Policy (CSP)
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                "style-src 'self' 'unsafe-inline'; " .
                "img-src 'self' data: https:; " .
                "font-src 'self' data:; " .
                "connect-src 'self' https://api.openai.com https://api.anthropic.com; " .
                "frame-ancestors 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self'"
        );

        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=()'
        );

        // Remove server information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
    }
}
