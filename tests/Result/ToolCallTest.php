<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Result;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Result\ToolCall;

#[CoversClass(ToolCall::class)]
#[Small]
final class ToolCallTest extends TestCase
{
    public function testToolCall()
    {
        $toolCall = new ToolCall('id', 'name', ['foo' => 'bar']);
        $this->assertSame('id', $toolCall->id);
        $this->assertSame('name', $toolCall->name);
        $this->assertSame(['foo' => 'bar'], $toolCall->arguments);
    }

    public function testToolCallJsonSerialize()
    {
        $toolCall = new ToolCall('id', 'name', ['foo' => 'bar']);
        $this->assertSame([
            'id' => 'id',
            'type' => 'function',
            'function' => [
                'name' => 'name',
                'arguments' => '{"foo":"bar"}',
            ],
        ], $toolCall->jsonSerialize());
    }
}
