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
 * @phpstan-type SafetyCheck array{id: string, code?: string|null, message?: string|null}
 *
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class ComputerCall implements ContentInterface
{
    /**
     * @param array<string, mixed> $action              The computer-use action the model asks the client to perform (e.g. a "click", "type" or "screenshot") together with its parameters
     * @param string|null          $callId              Identifier to echo back in the matching "computer_call_output" when returning the action result
     * @param list<SafetyCheck>    $pendingSafetyChecks Safety checks the client must acknowledge before executing the action
     * @param string|null          $id                  Identifier of the computer call output item (e.g. "cu_...")
     * @param string|null          $status              Provider-reported status of the call, e.g. "completed", "in_progress" or "incomplete"
     */
    public function __construct(
        private readonly array $action = [],
        private readonly ?string $callId = null,
        private readonly array $pendingSafetyChecks = [],
        private readonly ?string $id = null,
        private readonly ?string $status = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getAction(): array
    {
        return $this->action;
    }

    public function getCallId(): ?string
    {
        return $this->callId;
    }

    /**
     * @return list<SafetyCheck>
     */
    public function getPendingSafetyChecks(): array
    {
        return $this->pendingSafetyChecks;
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
