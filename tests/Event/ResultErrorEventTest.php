<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\Event\ResultErrorEvent;
use Symfony\AI\Platform\Exception\RuntimeException;
use Symfony\AI\Platform\Model;

final class ResultErrorEventTest extends TestCase
{
    public function testGettersReturnConstructorValues()
    {
        $model = new Model('test-model', [Capability::OUTPUT_TEXT]);
        $error = new RuntimeException('conversion failed');
        $options = ['temperature' => 0.7];

        $event = new ResultErrorEvent($model, $error, $options, 'Hello?');

        $this->assertSame($model, $event->getModel());
        $this->assertSame($error, $event->getError());
        $this->assertSame($options, $event->getOptions());
        $this->assertSame('Hello?', $event->getInput());
    }
}
