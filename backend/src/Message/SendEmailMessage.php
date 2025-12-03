<?php

declare(strict_types=1);

namespace App\Message;

final class SendEmailMessage
{
    public function __construct(
        private string $to,
        private string $subject,
        private string $content,
        private ?string $htmlContent = null,
    ) {}

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getHtmlContent(): ?string
    {
        return $this->htmlContent;
    }
}
