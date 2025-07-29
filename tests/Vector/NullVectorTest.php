<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Vector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Exception\RuntimeException;
use Symfony\AI\Platform\Vector\NullVector;
use Symfony\AI\Platform\Vector\VectorInterface;

#[CoversClass(NullVector::class)]
final class NullVectorTest extends TestCase
{
    public function testImplementsInterface()
    {
        $this->assertInstanceOf(VectorInterface::class, new NullVector());
    }

    public function testGetDataThrowsOnAccess()
    {
        self::expectException(RuntimeException::class);

        (new NullVector())->getData();
    }

    public function testGetDimensionsThrowsOnAccess()
    {
        self::expectException(RuntimeException::class);

        (new NullVector())->getDimensions();
    }
}
