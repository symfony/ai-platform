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

use Symfony\AI\Platform\Exception\InvalidArgumentException;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;

/**
 * Resolves a scripted response into a {@see ResultInterface}.
 *
 * The script can be:
 *  - a string: every call resolves to a {@see TextResult} wrapping it;
 *  - a map keyed by model name: per-model {@see ResultInterface} or string;
 *  - a \Closure(Model $model, array|string|object $input, array $options): ResultInterface|string.
 *
 * Bare strings are wrapped in a {@see TextResult}; a {@see ResultInterface} is returned verbatim,
 * so every result type is supported. Shared by the provider-level {@see MockModelClient} and the
 * platform-level {@see InMemoryPlatform} so both speak the same scripting language.
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ScriptedResponse
{
    /**
     * @param \Closure|string|array<string, ResultInterface|string> $script
     */
    public function __construct(
        private readonly \Closure|string|array $script,
    ) {
    }

    /**
     * @param array<string|int, mixed>|string|object $input
     * @param array<string, mixed>                   $options
     */
    public function resolve(Model $model, array|string|object $input, array $options): ResultInterface
    {
        $resolved = $this->resolveRaw($model, $input, $options);

        if (\is_string($resolved)) {
            return new TextResult($resolved);
        }

        return $resolved;
    }

    /**
     * @param array<string|int, mixed>|string|object $input
     * @param array<string, mixed>                   $options
     */
    private function resolveRaw(Model $model, array|string|object $input, array $options): ResultInterface|string
    {
        if ($this->script instanceof \Closure) {
            return ($this->script)($model, $input, $options);
        }

        if (\is_string($this->script)) {
            return $this->script;
        }

        $name = $model->getName();

        if (!\array_key_exists($name, $this->script)) {
            throw new InvalidArgumentException(\sprintf('No scripted response configured for model "%s".', $name));
        }

        return $this->script[$name];
    }
}
