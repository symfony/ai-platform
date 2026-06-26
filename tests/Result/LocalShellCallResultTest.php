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
use Symfony\AI\Platform\Result\LocalShellCallResult;

final class LocalShellCallResultTest extends TestCase
{
    public function testGetters()
    {
        $command = ['bash', '-lc', 'ls -la /tmp'];
        $result = new LocalShellCallResult($command, 'call_1', 'lsh_1', 'completed');

        $this->assertSame($command, $result->getContent());
        $this->assertSame('call_1', $result->getCallId());
        $this->assertSame('lsh_1', $result->getId());
        $this->assertSame('completed', $result->getStatus());
    }

    public function testDefaults()
    {
        $result = new LocalShellCallResult();

        $this->assertSame([], $result->getContent());
        $this->assertNull($result->getCallId());
        $this->assertNull($result->getId());
        $this->assertNull($result->getStatus());
    }
}
