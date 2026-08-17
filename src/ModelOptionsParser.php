<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform;

/**
 * Parses the query string of a model name into options.
 *
 * Behaves like parse_str(), except that it keeps periods in option names instead
 * of turning them into underscores, so that provider options like
 * "reasoning.effort" survive.
 *
 * @internal
 *
 * @author Saiful Islam Feroz <saif012@gmail.com>
 */
final class ModelOptionsParser
{
    /**
     * Marks periods while parse_str() runs. U+0001 cannot appear in a decoded
     * option name that came from a model string, so it never collides.
     */
    private const PERIOD_PLACEHOLDER = "\u{1}";

    /**
     * @return array<string, mixed>
     */
    public static function parse(string $queryString): array
    {
        // parse_str() replaces periods in option names with underscores, and does so
        // after decoding, so "%2E" is folded as well. Mask them for the duration of
        // the call: only the name of each pair is touched, values are left alone.
        $masked = preg_replace_callback(
            '/(?:^|(?<=&))[^=&]*/',
            static fn (array $matches): string => str_replace(['.', '%2E', '%2e'], self::PERIOD_PLACEHOLDER, $matches[0]),
            $queryString,
        );

        parse_str($masked ?? $queryString, $options);

        return self::restorePeriods($options);
    }

    /**
     * @param array<array-key, mixed> $options
     *
     * @return array<array-key, mixed>
     */
    private static function restorePeriods(array $options): array
    {
        $restored = [];

        foreach ($options as $name => $value) {
            if (\is_string($name)) {
                $name = str_replace(self::PERIOD_PLACEHOLDER, '.', $name);
            }

            $restored[$name] = \is_array($value) ? self::restorePeriods($value) : $value;
        }

        return $restored;
    }
}
