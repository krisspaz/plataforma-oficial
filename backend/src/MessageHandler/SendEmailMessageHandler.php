<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
final class SendEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger
    ) {}

    public function __invoke(SendEmailMessage $message): void
    {
        $this->logger->info('Sending email', [
            'to' => $message->getTo(),
            'subject' => $message->getSubject()
        ]);

        $email = (new Email())
            ->from('noreply@schoolplatform.com')
            ->to($message->getTo())
            ->subject($message->getSubject())
            ->text($message->getContent())
            ->html($message->getHtmlContent() ?? $message->getContent());

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email', [
                'error' => $e->getMessage(),
                'to' => $message->getTo()
            ]);
            throw $e; // Retry mechanism will handle this
        }
    }
}
