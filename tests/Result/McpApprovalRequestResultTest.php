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
use Symfony\AI\Platform\Result\McpApprovalRequestResult;

final class McpApprovalRequestResultTest extends TestCase
{
    public function testGetters()
    {
        $result = new McpApprovalRequestResult('deepwiki', 'ask_question', '{"q":"hi"}', 'mcpr_1');

        $this->assertSame('{"q":"hi"}', $result->getContent());
        $this->assertSame('deepwiki', $result->getServerLabel());
        $this->assertSame('ask_question', $result->getName());
        $this->assertSame('{"q":"hi"}', $result->getArguments());
        $this->assertSame('mcpr_1', $result->getId());
    }
}
