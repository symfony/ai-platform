<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Result\Stream\Delta;

/**
 * Carries a progressively populated typed object recovered from a streamed
 * structured-output response. Emitted by `PartialObjectStreamListener` for
 * every delta in which the recovered JSON structure has changed.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
final class PartialObjectDelta implements DeltaInterface
{
    /**
     * @param object|array<mixed> $object
     */
    public function __construct(
        private readonly object|array $object,
        private readonly string $buffer,
    ) {
    }

    /**
     * @return object|array<mixed>
     */
    public function getObject(): object|array
    {
        return $this->object;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
