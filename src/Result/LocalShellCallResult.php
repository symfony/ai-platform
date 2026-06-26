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
 * Local shell command requested by the model, to be executed by the client
 * (e.g. the OpenAI Responses `local_shell_call` output item).
 *
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class LocalShellCallResult extends BaseResult
{
    /**
     * @param list<string> $command The shell command (as an argv list) the model asks the client to execute
     * @param string|null  $callId  Identifier to echo back in the matching "local_shell_call_output" when returning the command result
     * @param string|null  $id      Identifier of the local shell call output item (e.g. "lsh_...")
     * @param string|null  $status  Provider-reported status of the call, e.g. "completed", "in_progress" or "incomplete"
     */
    public function __construct(
        private readonly array $command = [],
        private readonly ?string $callId = null,
        private readonly ?string $id = null,
        private readonly ?string $status = null,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getContent(): array
    {
        return $this->command;
    }

    public function getCallId(): ?string
    {
        return $this->callId;
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
