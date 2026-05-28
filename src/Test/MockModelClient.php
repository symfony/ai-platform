<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Test;

use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\ModelClientInterface;
use Symfony\AI\Platform\Result\InMemoryRawResult;
use Symfony\AI\Platform\Result\ResultInterface;

/**
 * Test model client that returns scripted responses and records every call.
 *
 * The scripted response is resolved by {@see ScriptedResponse} and threaded through unchanged via
 * the `object` slot of an {@see InMemoryRawResult}, so every result type is supported without
 * per-type conversion code.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
final class MockModelClient implements ModelClientInterface
{
    /**
     * @var list<array{model: Model, payload: array<string|int, mixed>|string, options: array<string, mixed>}>
     */
    private array $calls = [];

    private readonly ScriptedResponse $response;

    /**
     * @param \Closure|string|array<string, ResultInterface|string> $responses
     */
    public function __construct(\Closure|string|array $responses)
    {
        $this->response = new ScriptedResponse($responses);
    }

    public function supports(Model $model): bool
    {
        return true;
    }

    /**
     * @param array<string|int, mixed> $payload
     * @param array<string, mixed>     $options
     */
    public function request(Model $model, array|string $payload, array $options = []): InMemoryRawResult
    {
        $this->calls[] = ['model' => $model, 'payload' => $payload, 'options' => $options];

        return new InMemoryRawResult(object: $this->response->resolve($model, $payload, $options));
    }

    /**
     * @return list<array{model: Model, payload: array<string|int, mixed>|string, options: array<string, mixed>}>
     */
    public function getCalls(): array
    {
        return $this->calls;
    }
}
