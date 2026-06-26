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

use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Result\McpCallResult;

final class McpCallResultTest extends TestCase
{
    public function testGetters()
    {
        $result = new McpCallResult('deepwiki', 'ask_question', '{"q":"hi"}', 'the answer', null, 'mcp_1', 'completed');

        $this->assertSame('the answer', $result->getContent());
        $this->assertSame('deepwiki', $result->getServerLabel());
        $this->assertSame('ask_question', $result->getName());
        $this->assertSame('{"q":"hi"}', $result->getArguments());
        $this->assertNull($result->getError());
        $this->assertSame('mcp_1', $result->getId());
        $this->assertSame('completed', $result->getStatus());
    }

    public function testError()
    {
        $result = new McpCallResult('deepwiki', 'ask_question', null, null, 'boom', 'mcp_2', 'failed');

        $this->assertNull($result->getContent());
        $this->assertSame('boom', $result->getError());
    }
}
