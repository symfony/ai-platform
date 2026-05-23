<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\StructuredOutput\Streaming;

/**
 * Best-effort parser for incomplete JSON strings produced by streaming structured output.
 *
 * Attempts a strict `json_decode` first and falls back to a sequence of recovery passes
 * (trailing commas, unclosed strings, partial literals, unclosed structures) so callers
 * can render partial objects before a model finishes emitting them.
 *
 * Byte-indexed scanning is intentional: `"` and `\` are always single-byte in UTF-8,
 * so the string/escape tracking remains correct even for multi-byte payloads.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
final class PartialJsonParser
{
    /**
     * @param-out string|null $errorMessage null on success, json_last_error_msg() text on failure
     */
    public static function parse(string $json, ?string &$errorMessage = null): mixed
    {
        $data = @json_decode($json, true);

        if (\JSON_ERROR_NONE === json_last_error()) {
            $errorMessage = null;

            return $data;
        }

        $errorMessage = json_last_error_msg();

        return self::fixAndParse($json, $errorMessage);
    }

    private static function fixAndParse(string $json, ?string &$errorMessage): mixed
    {
        $fixed = self::fixSyntax($json);

        $data = @json_decode($fixed, true);

        if (\JSON_ERROR_NONE === json_last_error()) {
            $errorMessage = null;

            return $data;
        }

        $errorMessage = json_last_error_msg();

        return null;
    }

    private static function fixSyntax(string $json): string
    {
        $json = self::removeTrailingCommas($json);
        $json = self::closeUnclosedStrings($json);
        $json = self::fixIncompleteValues($json);
        $json = self::removeDanglingObjectKey($json);

        return self::closeUnclosedStructures($json);
    }

    /**
     * Removes commas that immediately precede `]`/`}` or the end of the buffer.
     *
     * The scan is string-aware: a comma inside a string value is content and must be preserved,
     * otherwise payloads like `{"k":"a,]` would be silently corrupted to `a]`.
     */
    private static function removeTrailingCommas(string $json): string
    {
        $inString = false;
        $escaped = false;
        $length = \strlen($json);
        $result = '';

        for ($i = 0; $i < $length; ++$i) {
            $char = $json[$i];

            if ('"' === $char && !$escaped) {
                $inString = !$inString;
            }

            $escaped = '\\' === $char && !$escaped;

            if (!$inString && ',' === $char) {
                $next = $i + 1;
                while ($next < $length && ctype_space($json[$next])) {
                    ++$next;
                }

                if ($next >= $length || ']' === $json[$next] || '}' === $json[$next]) {
                    continue;
                }
            }

            $result .= $char;
        }

        return $result;
    }

    private static function closeUnclosedStrings(string $json): string
    {
        $inString = false;
        $escaped = false;
        $length = \strlen($json);

        for ($i = 0; $i < $length; ++$i) {
            $char = $json[$i];

            if ('"' === $char && !$escaped) {
                $inString = !$inString;
            }

            $escaped = '\\' === $char && !$escaped;
        }

        if ($inString) {
            $json = self::trimIncompleteTrailingEscape($json);
            $json .= '"';
        }

        return $json;
    }

    /**
     * Drops a trailing incomplete escape sequence inside an unterminated string so the closing
     * quote appended by {@see closeUnclosedStrings()} is not swallowed (`{"a":"hello\`) and no
     * invalid unicode escape is left behind (`{"a":"\u00`).
     */
    private static function trimIncompleteTrailingEscape(string $json): string
    {
        if (preg_match('/\\\\u[0-9a-fA-F]{0,3}$/', $json, $matches, \PREG_OFFSET_CAPTURE)) {
            $offset = $matches[0][1];

            if (self::isEscapeIntroducer($json, $offset)) {
                return substr($json, 0, $offset);
            }
        }

        $last = \strlen($json) - 1;
        if ($last >= 0 && '\\' === $json[$last] && self::isEscapeIntroducer($json, $last)) {
            return substr($json, 0, -1);
        }

        return $json;
    }

    /**
     * Determines whether the backslash at the given position starts an escape sequence, i.e. it is
     * not itself escaped by an even number of preceding backslashes.
     */
    private static function isEscapeIntroducer(string $json, int $position): bool
    {
        $backslashes = 0;
        for ($i = $position - 1; $i >= 0 && '\\' === $json[$i]; --$i) {
            ++$backslashes;
        }

        return 0 === $backslashes % 2;
    }

    private static function fixIncompleteValues(string $json): string
    {
        $json = self::completePartialLiterals($json);
        $json = self::truncateIncompleteNumber($json);

        $trimmed = rtrim($json);
        if (preg_match('/:\s*$/', $trimmed)) {
            return $trimmed.'null';
        }

        return $json;
    }

    private static function completePartialLiterals(string $json): string
    {
        $trimmed = rtrim($json);

        if (preg_match('/([\s:,\[\{])tru$/i', $trimmed)) {
            return substr($trimmed, 0, -3).'true';
        }
        if (preg_match('/([\s:,\[\{])tr$/i', $trimmed)) {
            return substr($trimmed, 0, -2).'true';
        }
        if (preg_match('/([\s:,\[\{])fals$/i', $trimmed)) {
            return substr($trimmed, 0, -4).'false';
        }
        if (preg_match('/([\s:,\[\{])fal$/i', $trimmed)) {
            return substr($trimmed, 0, -3).'false';
        }
        if (preg_match('/([\s:,\[\{])fa$/i', $trimmed)) {
            return substr($trimmed, 0, -2).'false';
        }
        if (preg_match('/([\s:,\[\{])nul$/i', $trimmed)) {
            return substr($trimmed, 0, -3).'null';
        }
        if (preg_match('/([\s:,\[\{])nu$/i', $trimmed)) {
            return substr($trimmed, 0, -2).'null';
        }

        return $json;
    }

    /**
     * Truncates a trailing, not-yet-complete number to its longest valid prefix so streamed deltas
     * such as `{"a": 1.`, `{"a": 1e` or `{"a": -` no longer abort the whole recovery.
     */
    private static function truncateIncompleteNumber(string $json): string
    {
        $trimmed = rtrim($json);

        if (!preg_match('/[:,\[]\s*([-+0-9.eE]+)$/', $trimmed, $matches, \PREG_OFFSET_CAPTURE)) {
            return $json;
        }

        $token = $matches[1][0];
        $valid = self::longestValidNumberPrefix($token);

        if ($valid === $token) {
            return $json;
        }

        return substr($trimmed, 0, $matches[1][1]).$valid;
    }

    private static function longestValidNumberPrefix(string $token): string
    {
        for ($length = \strlen($token); $length > 0; --$length) {
            $candidate = substr($token, 0, $length);

            if (preg_match('/^-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+-]?\d+)?$/', $candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * Drops a trailing object key that has no value yet (`{"a":1,"bb`). The scan tracks the
     * enclosing container so that a trailing string in array context (`["foo", "bar`) is preserved
     * as a value rather than mistaken for a key.
     */
    private static function removeDanglingObjectKey(string $json): string
    {
        $stack = [];
        $inString = false;
        $escaped = false;
        $length = \strlen($json);

        $stringStart = -1;
        $lastStringStart = -1;
        $lastStringEnd = -1;
        $containerAtLastString = '';

        for ($i = 0; $i < $length; ++$i) {
            $char = $json[$i];

            if ($inString) {
                if ('"' === $char && !$escaped) {
                    $inString = false;
                    $lastStringStart = $stringStart;
                    $lastStringEnd = $i + 1;
                    $containerAtLastString = [] !== $stack ? $stack[\count($stack) - 1] : '';
                }

                $escaped = '\\' === $char && !$escaped;

                continue;
            }

            if ('"' === $char) {
                $inString = true;
                $stringStart = $i;
                $escaped = false;

                continue;
            }

            if ('[' === $char || '{' === $char) {
                $stack[] = $char;
            } elseif (']' === $char || '}' === $char) {
                array_pop($stack);
            }
        }

        if ($inString || -1 === $lastStringStart || '{' !== $containerAtLastString) {
            return $json;
        }

        if ('' !== trim(substr($json, $lastStringEnd))) {
            return $json;
        }

        $head = rtrim(substr($json, 0, $lastStringStart));

        if (str_ends_with($head, ',')) {
            return substr($head, 0, -1);
        }

        if (str_ends_with($head, '{')) {
            return $head;
        }

        return $json;
    }

    private static function closeUnclosedStructures(string $json): string
    {
        $stack = [];
        $inString = false;
        $escaped = false;
        $length = \strlen($json);

        for ($i = 0; $i < $length; ++$i) {
            $char = $json[$i];

            if ('"' === $char && !$escaped) {
                $inString = !$inString;
            }

            $escaped = '\\' === $char && !$escaped;

            if ($inString) {
                continue;
            }

            if ('[' === $char || '{' === $char) {
                $stack[] = $char;
            } elseif (']' === $char) {
                if ([] !== $stack && '[' === $stack[\count($stack) - 1]) {
                    array_pop($stack);
                }
            } elseif ('}' === $char) {
                if ([] !== $stack && '{' === $stack[\count($stack) - 1]) {
                    array_pop($stack);
                }
            }
        }

        while ([] !== $stack) {
            $open = array_pop($stack);
            $json .= '[' === $open ? ']' : '}';
        }

        return $json;
    }
}
