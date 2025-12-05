<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Security Audit Logger
 * Centralizes all security-related logging events
 */
class SecurityAuditListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $securityLogger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => 'onAuthenticationSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            LogoutEvent::class => 'onLogout',
            KernelEvents::REQUEST => ['onRequest', -10],
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        $this->securityLogger->info('Authentication successful', [
            'username' => $user->getUserIdentifier(),
            'ip' => $this->getClientIp(),
            'timestamp' => (new \DateTime())->format('c'),
            'event' => 'LOGIN_SUCCESS',
        ]);
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();

        $this->securityLogger->warning('Authentication failed', [
            'username' => $request->request->get('email') ?? $request->request->get('username'),
            'ip' => $request->getClientIp(),
            'userAgent' => $request->headers->get('User-Agent'),
            'timestamp' => (new \DateTime())->format('c'),
            'event' => 'LOGIN_FAILURE',
            'exception' => $event->getException()->getMessage(),
        ]);
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();

        if ($token && $token->getUser()) {
            $this->securityLogger->info('User logged out', [
                'username' => $token->getUser()->getUserIdentifier(),
                'ip' => $event->getRequest()?->getClientIp(),
                'timestamp' => (new \DateTime())->format('c'),
                'event' => 'LOGOUT',
            ]);
        }
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Log suspicious requests
        if ($this->isSuspiciousRequest($request)) {
            $this->securityLogger->warning('Suspicious request detected', [
                'ip' => $request->getClientIp(),
                'uri' => $request->getRequestUri(),
                'method' => $request->getMethod(),
                'userAgent' => $request->headers->get('User-Agent'),
                'timestamp' => (new \DateTime())->format('c'),
                'event' => 'SUSPICIOUS_REQUEST',
            ]);
        }
    }

    private function isSuspiciousRequest(Request $request): bool
    {
        $uri = $request->getRequestUri();
        $suspiciousPatterns = [
            '/\.\.\//',           // Path traversal
            '/\<script/i',        // XSS attempts
            '/union\s+select/i',  // SQL injection
            '/eval\s*\(/i',       // Code injection
            '/base64_decode/i',   // PHP injection
            '/admin\/?$/i',       // Admin access attempts
            '/wp-admin/i',        // WordPress probing
            '/phpmyadmin/i',      // phpMyAdmin probing
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                return true;
            }
        }

        return false;
    }

    private function getClientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'unknown';
    }
}
