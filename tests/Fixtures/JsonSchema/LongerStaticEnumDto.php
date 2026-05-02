<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Fixtures\JsonSchema;

use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;

/**
 * The static enum intentionally holds more values than the provider returns, so an
 * index-wise merge (array_replace_recursive) would leak the trailing static value.
 */
final class LongerStaticEnumDto
{
    public function __construct(
        #[Schema(enum: ['a', 'b', 'c'], provider: StatusProvider::class)]
        public readonly string $status,
    ) {
    }
}
