<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Result\Stream\Delta;

use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Result\Stream\Delta\PartialObjectDelta;

final class PartialObjectDeltaTest extends TestCase
{
    public function testGettersExposeConstructorArguments()
    {
        $object = new \stdClass();
        $object->name = 'Symfony';

        $delta = new PartialObjectDelta($object, '{"name":"Symfony');

        $this->assertSame($object, $delta->getObject());
        $this->assertSame('{"name":"Symfony', $delta->getBuffer());
    }

    public function testAcceptsArrayObject()
    {
        $delta = new PartialObjectDelta(['name' => 'Symfony'], '{"name":"Symfony"}');

        $this->assertSame(['name' => 'Symfony'], $delta->getObject());
        $this->assertSame('{"name":"Symfony"}', $delta->getBuffer());
    }
}
