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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\ModelOptionsParser;

final class ModelOptionsParserTest extends TestCase
{
    public function testPeriodsInOptionNamesArePreserved()
    {
        $this->assertSame(['reasoning.effort' => 'medium'], ModelOptionsParser::parse('reasoning.effort=medium'));
    }

    public function testPeriodsArePreservedInNestedOptionNames()
    {
        $this->assertSame(['a.b' => ['c.d' => '1']], ModelOptionsParser::parse('a.b[c.d]=1'));
    }

    public function testEncodedPeriodsInOptionNamesArePreserved()
    {
        $this->assertSame(['reasoning.effort' => 'medium'], ModelOptionsParser::parse('reasoning%2Eeffort=medium'));
    }

    public function testPeriodsInValuesAreUntouched()
    {
        $this->assertSame(['temperature' => '0.7'], ModelOptionsParser::parse('temperature=0.7'));
    }

    /**
     * Everything without a period in an option name has to keep behaving exactly like parse_str().
     */
    #[DataProvider('provideQueryStringsWithoutPeriodsInNames')]
    public function testBehavesLikeParseStrOtherwise(string $queryString)
    {
        parse_str($queryString, $expected);

        $this->assertSame($expected, ModelOptionsParser::parse($queryString));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideQueryStringsWithoutPeriodsInNames(): iterable
    {
        yield 'empty' => [''];
        yield 'flat' => ['think=true&stream=false'];
        yield 'value without name' => ['flag'];
        yield 'nested arrays' => ['options[max_tokens]=500&options[metadata][version]=1'];
        yield 'deeply nested arrays' => ['a[b][c]=123&a[b][d]=text&a[e]=456'];
        yield 'appended values' => ['x[]=1&x[]=2'];
        yield 'repeated name' => ['a=1&a=2'];
        yield 'encoded value' => ['key=val%20ue'];
        yield 'encoded ampersand in value' => ['e=a%26b'];
        yield 'period in value' => ['frequency_penalty=1e-5&presence_penalty=2.5E3'];
    }
}
