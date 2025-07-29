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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Result\Choice;
use Symfony\AI\Platform\Result\ToolCall;

#[CoversClass(Choice::class)]
#[UsesClass(ToolCall::class)]
#[Small]
final class ChoiceTest extends TestCase
{
    public function testChoiceEmpty()
    {
        $choice = new Choice();
        $this->assertFalse($choice->hasContent());
        $this->assertNull($choice->getContent());
        $this->assertFalse($choice->hasToolCall());
        $this->assertCount(0, $choice->getToolCalls());
    }

    public function testChoiceWithContent()
    {
        $choice = new Choice('content');
        $this->assertTrue($choice->hasContent());
        $this->assertSame('content', $choice->getContent());
        $this->assertFalse($choice->hasToolCall());
        $this->assertCount(0, $choice->getToolCalls());
    }

    public function testChoiceWithToolCall()
    {
        $choice = new Choice(null, [new ToolCall('name', 'arguments')]);
        $this->assertFalse($choice->hasContent());
        $this->assertNull($choice->getContent());
        $this->assertTrue($choice->hasToolCall());
        $this->assertCount(1, $choice->getToolCalls());
    }

    public function testChoiceWithContentAndToolCall()
    {
        $choice = new Choice('content', [new ToolCall('name', 'arguments')]);
        $this->assertTrue($choice->hasContent());
        $this->assertSame('content', $choice->getContent());
        $this->assertTrue($choice->hasToolCall());
        $this->assertCount(1, $choice->getToolCalls());
    }
}
