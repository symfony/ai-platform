<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Bridge\Ollama;

/**
 * @author Shaun Johnston <shaun@snj.au>
 */
final class OllamaMessageChunk
{
    /**
     * @param array<string, mixed> $message
     */
    public function __construct(
        public readonly string $model,
        public readonly \DateTimeImmutable $created_at,
        public readonly array $message,
        public readonly bool $done,
    ) {
    }

    public function __toString(): string
    {
        // Return the assistant's message content if available
        return $this->message['content'] ?? '';
    }

    public function getContent(): ?string
    {
        return $this->message['content'] ?? null;
    }

    public function getRole(): ?string
    {
        return $this->message['role'] ?? null;
    }
}
