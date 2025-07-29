<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Message\Content;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Message\Content\Text;

#[CoversClass(Text::class)]
#[Small]
final class TextTest extends TestCase
{
    public function testConstructionIsPossible()
    {
        $obj = new Text('foo');

        $this->assertSame('foo', $obj->text);
    }
}
