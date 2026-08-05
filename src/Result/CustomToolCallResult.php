<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Result;

/**
 * Result of a custom tool invoked server-side by the model as part of a built-in tool
 * (e.g. xAI's `x_search` reporting its own `custom_tool_call` sub-calls). Unlike
 * {@see ToolCall}, the provider has already resolved the call by the time it is reported,
 * so it is exposed as an informational result rather than something the application is
 * expected to execute and answer.
 *
 * @author Valtteri R <valtzu@gmail.com>
 */
final class CustomToolCallResult extends BaseResult
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

    public function getContent(): string
    {
        return $this->input;
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
