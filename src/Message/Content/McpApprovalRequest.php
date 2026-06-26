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
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class McpApprovalRequest implements ContentInterface
{
    /**
     * @param string      $serverLabel Label of the hosted MCP server requesting approval before the tool runs
     * @param string      $name        Name of the MCP tool the model wants to call
     * @param string|null $arguments   JSON-encoded arguments the tool would be called with
     * @param string|null $id          Identifier of the approval request, referenced when sending the approval response back (e.g. "mcpr_...")
     */
    public function __construct(
        private readonly string $serverLabel,
        private readonly string $name,
        private readonly ?string $arguments = null,
        private readonly ?string $id = null,
    ) {
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

    public function getId(): ?string
    {
        return $this->id;
    }
}
