<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\Model;

#[CoversClass(Model::class)]
#[Small]
#[UsesClass(Capability::class)]
final class ModelTest extends TestCase
{
    #[Test]
    public function returnsName(): void
    {
        $model = new Model('gpt-4');

        $this->assertSame('gpt-4', $model->getName());
    }

    #[Test]
    public function returnsCapabilities(): void
    {
        $model = new Model('gpt-4', [Capability::INPUT_TEXT, Capability::OUTPUT_TEXT]);

        $this->assertSame([Capability::INPUT_TEXT, Capability::OUTPUT_TEXT], $model->getCapabilities());
    }

    #[Test]
    public function checksSupportForCapability(): void
    {
        $model = new Model('gpt-4', [Capability::INPUT_TEXT, Capability::OUTPUT_TEXT]);

        $this->assertTrue($model->supports(Capability::INPUT_TEXT));
        $this->assertTrue($model->supports(Capability::OUTPUT_TEXT));
        $this->assertFalse($model->supports(Capability::INPUT_IMAGE));
    }

    #[Test]
    public function returnsEmptyCapabilitiesByDefault(): void
    {
        $model = new Model('gpt-4');

        $this->assertSame([], $model->getCapabilities());
    }

    #[Test]
    public function returnsOptions(): void
    {
        $options = [
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ];
        $model = new Model('gpt-4', [], $options);

        $this->assertSame($options, $model->getOptions());
    }

    #[Test]
    public function returnsEmptyOptionsByDefault(): void
    {
        $model = new Model('gpt-4');

        $this->assertSame([], $model->getOptions());
    }
}
