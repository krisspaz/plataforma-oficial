<?php

declare(strict_types=1);

namespace App\Domain\Contract\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing a contract template.
 */
final class ContractTemplate
{
    private readonly string $name;
    private readonly string $content;
    private readonly array $variables;

    public function __construct(string $name, string $content, array $variables = [])
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Template name cannot be empty');
        }

        if (empty(trim($content))) {
            throw new InvalidArgumentException('Template content cannot be empty');
        }

        $this->name = trim($name);
        $this->content = $content;
        $this->variables = $variables;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            $data['content'] ?? '',
            $data['variables'] ?? []
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Render template with provided data.
     */
    public function render(array $data): string
    {
        $content = $this->content;

        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $content = str_replace($placeholder, (string) $value, $content);
        }

        return $content;
    }

    /**
     * Validate that all required variables are present in data.
     */
    public function validateData(array $data): array
    {
        $missing = [];

        foreach ($this->variables as $variable) {
            if (!isset($data[$variable])) {
                $missing[] = $variable;
            }
        }

        return $missing;
    }

    /**
     * Extract variables from template content.
     */
    public static function extractVariables(string $content): array
    {
        preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $content, $matches);
        return array_unique($matches[1] ?? []);
    }

    public function hasVariable(string $variable): bool
    {
        return in_array($variable, $this->variables, true);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'content' => $this->content,
            'variables' => $this->variables,
        ];
    }
}
