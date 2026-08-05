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
use Symfony\AI\Platform\Result\CustomToolCallResult;

final class CustomToolCallResultTest extends TestCase
{
    public function testGetters()
    {
        $result = new CustomToolCallResult('x_keyword_search', '{"query":"BETR stock"}', 'ctc_1', 'completed');

        $this->assertSame('{"query":"BETR stock"}', $result->getContent());
        $this->assertSame('x_keyword_search', $result->getName());
        $this->assertSame('{"query":"BETR stock"}', $result->getInput());
        $this->assertSame('ctc_1', $result->getId());
        $this->assertSame('completed', $result->getStatus());
    }

    public function testDefaults()
    {
        $result = new CustomToolCallResult('run_sql', 'SELECT * FROM users');

        $this->assertNull($result->getId());
        $this->assertNull($result->getStatus());
    }
}
