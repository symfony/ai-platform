<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform;

use Symfony\AI\Platform\Result\InMemoryRawResult;
use Symfony\AI\Platform\Result\ResultPromise;
use Symfony\AI\Platform\Result\TextResult;

/**
 * A fake implementation of PlatformInterface that returns fixed or callable responses.
 *
 * Useful for unit or integration testing without real API calls.
 *
 * @author Ramy Hakam <pencilsoft1@gmail.com>
 */
class InMemoryPlatform implements PlatformInterface
{
    /**
     * The mock result can be a string or a callable that returns a string.
     * If it's a closure, it receives the model, input, and  optionally options as parameters like a real platform call.
     */
    public function __construct(private readonly \Closure|string $mockResult)
    {
    }

    public function invoke(Model $model, array|string|object $input, array $options = []): ResultPromise
    {
        $resultText = $this->mockResult instanceof \Closure
            ? ($this->mockResult)($model, $input, $options)
            : $this->mockResult;

        $textResult = new TextResult($resultText);

        return new ResultPromise(
            static fn () => $textResult,
            rawResult: new InMemoryRawResult(
                ['text' => $resultText],
                (object) ['text' => $resultText],
            ),
            options: $options
        );
    }
}
