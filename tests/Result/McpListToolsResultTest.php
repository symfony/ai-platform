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
use Symfony\AI\Platform\Result\McpListToolsResult;

final class McpListToolsResultTest extends TestCase
{
    public function testGetters()
    {
        $tools = [['name' => 'ask_question', 'description' => 'Ask a question.']];
        $result = new McpListToolsResult('deepwiki', $tools, 'mcpl_1');

        $this->assertSame($tools, $result->getContent());
        $this->assertSame('deepwiki', $result->getServerLabel());
        $this->assertSame('mcpl_1', $result->getId());
    }

    public function testDefaults()
    {
        $result = new McpListToolsResult('deepwiki');

        $this->assertSame([], $result->getContent());
        $this->assertNull($result->getId());
    }
}
