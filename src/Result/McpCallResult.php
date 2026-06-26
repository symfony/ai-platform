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
 * Result of a hosted MCP tool invocation performed server-side by the model
 * (e.g. the OpenAI Responses `mcp_call` output item).
 *
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class McpCallResult extends BaseResult
{
    /**
     * @param string      $serverLabel Label of the hosted MCP server the invoked tool belongs to
     * @param string      $name        Name of the MCP tool that was invoked
     * @param string|null $arguments   JSON-encoded arguments the tool was called with
     * @param string|null $output      Output returned by the tool, or null when the call produced none or failed
     * @param string|null $error       Error message when the call failed, otherwise null
     * @param string|null $id          Identifier of the MCP call output item (e.g. "mcp_...")
     * @param string|null $status      Provider-reported status of the call, e.g. "completed", "calling" or "failed"
     */
    public function __construct(
        private readonly string $serverLabel,
        private readonly string $name,
        private readonly ?string $arguments = null,
        private readonly ?string $output = null,
        private readonly ?string $error = null,
        private readonly ?string $id = null,
        private readonly ?string $status = null,
    ) {
    }

    public function getContent(): ?string
    {
        return $this->output;
    }

    public function getServerLabel(): string
    {
        return $this->serverLabel;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function getError(): ?string
    {
        return $this->error;
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
