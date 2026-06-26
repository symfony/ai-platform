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
use Symfony\AI\Platform\Result\FileSearchResult;

final class FileSearchResultTest extends TestCase
{
    public function testGetters()
    {
        $results = [['file_id' => 'file-1', 'filename' => 'doc.pdf', 'text' => 'lorem', 'score' => 0.9]];
        $result = new FileSearchResult(['What is deep research?'], $results, 'fs_1', 'completed');

        $this->assertSame($results, $result->getContent());
        $this->assertSame(['What is deep research?'], $result->getQueries());
        $this->assertSame('fs_1', $result->getId());
        $this->assertSame('completed', $result->getStatus());
    }

    public function testDefaults()
    {
        $result = new FileSearchResult();

        $this->assertSame([], $result->getContent());
        $this->assertSame([], $result->getQueries());
        $this->assertNull($result->getId());
        $this->assertNull($result->getStatus());
    }
}
