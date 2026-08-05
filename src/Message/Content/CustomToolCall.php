<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Message\Content;

/**
 * @author Valtteri R <valtzu@gmail.com>
 */
final class CustomToolCall implements ContentInterface
{
    /**
     * @param string      $name   Name of the custom tool that was invoked
     * @param string      $input  Freeform text input the tool was called with, as reported by the provider
     * @param string|null $id     Identifier of the custom tool call output item (e.g. "ctc_...")
     * @param string|null $status Provider-reported status of the call, e.g. "completed" or "failed"
     */
    public function __construct(
        private readonly string $name,
        private readonly string $input,
        private readonly ?string $id = null,
        private readonly ?string $status = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }
}
