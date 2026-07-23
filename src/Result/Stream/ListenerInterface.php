<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Result\Stream;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface ListenerInterface
{
    /**
     * Dispatched once, before the first delta is consumed.
     */
    public function onStart(StartEvent $event): void;

    /**
     * Dispatched for every delta, before it is yielded to the consumer.
     *
     * The listener can rewrite or skip the delta through the event.
     */
    public function onDelta(DeltaEvent $event): void;

    /**
     * Dispatched once the stream drained without error.
     *
     * Terminal, and mutually exclusive with onError().
     */
    public function onComplete(CompleteEvent $event): void;

    /**
     * Dispatched when draining the stream throws, right before the exception is rethrown to the caller.
     *
     * Terminal, and mutually exclusive with onComplete(): a stream that reported its token usage on a
     * delta and then failed reaches this one only, so a listener with a finalizing side effect (cost
     * accounting, quota debit, audit) has to act on both. A stream the consumer abandons reaches neither.
     */
    public function onError(ErrorEvent $event): void;
}
