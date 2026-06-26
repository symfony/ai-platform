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
use Symfony\AI\Platform\Result\ComputerCallResult;

final class ComputerCallResultTest extends TestCase
{
    public function testGetters()
    {
        $action = ['type' => 'click', 'button' => 'left', 'x' => 510, 'y' => 372];
        $safetyChecks = [['id' => 'cu_sc_1', 'code' => 'malicious_instructions', 'message' => 'careful']];
        $result = new ComputerCallResult($action, 'call_1', $safetyChecks, 'cu_1', 'completed');

        $this->assertSame($action, $result->getContent());
        $this->assertSame('call_1', $result->getCallId());
        $this->assertSame($safetyChecks, $result->getPendingSafetyChecks());
        $this->assertSame('cu_1', $result->getId());
        $this->assertSame('completed', $result->getStatus());
    }

    public function testDefaults()
    {
        $result = new ComputerCallResult();

        $this->assertSame([], $result->getContent());
        $this->assertSame([], $result->getPendingSafetyChecks());
        $this->assertNull($result->getCallId());
        $this->assertNull($result->getId());
        $this->assertNull($result->getStatus());
    }
}
