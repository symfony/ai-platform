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
use Symfony\AI\Platform\Event\ResultConvertedEvent;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\Result\TextResult;

final class ResultConvertedEventTest extends TestCase
{
    public function testGettersReturnConstructorValues()
    {
        $model = new Model('test-model', [Capability::OUTPUT_TEXT]);
        $result = new TextResult('Hello');
        $options = ['temperature' => 0.7];

        $event = new ResultConvertedEvent($model, $result, $options, 'Hello?');

        $this->assertSame($model, $event->getModel());
        $this->assertSame($result, $event->getResult());
        $this->assertSame($options, $event->getOptions());
        $this->assertSame('Hello?', $event->getInput());
    }

    public function testSetResultOverridesResolvedResult()
    {
        $event = new ResultConvertedEvent(new Model('test-model', [Capability::OUTPUT_TEXT]), new TextResult('Hello'));

        $newResult = new TextResult('World');
        $event->setResult($newResult);

        $this->assertSame($newResult, $event->getResult());
    }
}
