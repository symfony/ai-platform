<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Helper;

trait UuidAssertionTrait
{
    /**
     * Asserts that a value is a valid UUID v7 string.
     */
    public static function assertIsUuidV7(mixed $actual, string $message = ''): void
    {
        self::assertIsString($actual, $message ?: 'Failed asserting that value is a string.');
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $actual,
            $message ?: 'Failed asserting that value is a valid UUID v7.'
        );
    }
}
