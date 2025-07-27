<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Contract\JsonSchema\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;
use Symfony\AI\Platform\Exception\InvalidArgumentException;

#[CoversClass(With::class)]
final class ToolParameterTest extends TestCase
{
    public function testValidEnum(): void
    {
        $enum = ['value1', 'value2'];
        $toolParameter = new With(enum: $enum);
        $this->assertSame($enum, $toolParameter->enum);
    }

    public function testInvalidEnumContainsNonString(): void
    {
        self::expectException(InvalidArgumentException::class);
        $enum = ['value1', 2];
        new With(enum: $enum);
    }

    public function testValidConstString(): void
    {
        $const = 'constant value';
        $toolParameter = new With(const: $const);
        $this->assertSame($const, $toolParameter->const);
    }

    public function testInvalidConstEmptyString(): void
    {
        self::expectException(InvalidArgumentException::class);
        $const = '   ';
        new With(const: $const);
    }

    public function testValidPattern(): void
    {
        $pattern = '/^[a-z]+$/';
        $toolParameter = new With(pattern: $pattern);
        $this->assertSame($pattern, $toolParameter->pattern);
    }

    public function testInvalidPatternEmptyString(): void
    {
        self::expectException(InvalidArgumentException::class);
        $pattern = '   ';
        new With(pattern: $pattern);
    }

    public function testValidMinLength(): void
    {
        $minLength = 5;
        $toolParameter = new With(minLength: $minLength);
        $this->assertSame($minLength, $toolParameter->minLength);
    }

    public function testInvalidMinLengthNegative(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(minLength: -1);
    }

    public function testValidMinLengthAndMaxLength(): void
    {
        $minLength = 5;
        $maxLength = 10;
        $toolParameter = new With(minLength: $minLength, maxLength: $maxLength);
        $this->assertSame($minLength, $toolParameter->minLength);
        $this->assertSame($maxLength, $toolParameter->maxLength);
    }

    public function testInvalidMaxLengthLessThanMinLength(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(minLength: 10, maxLength: 5);
    }

    public function testValidMinimum(): void
    {
        $minimum = 0;
        $toolParameter = new With(minimum: $minimum);
        $this->assertSame($minimum, $toolParameter->minimum);
    }

    public function testInvalidMinimumNegative(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(minimum: -1);
    }

    public function testValidMultipleOf(): void
    {
        $multipleOf = 5;
        $toolParameter = new With(multipleOf: $multipleOf);
        $this->assertSame($multipleOf, $toolParameter->multipleOf);
    }

    public function testInvalidMultipleOfNegative(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(multipleOf: -5);
    }

    public function testValidExclusiveMinimumAndMaximum(): void
    {
        $exclusiveMinimum = 1;
        $exclusiveMaximum = 10;
        $toolParameter = new With(exclusiveMinimum: $exclusiveMinimum, exclusiveMaximum: $exclusiveMaximum);
        $this->assertSame($exclusiveMinimum, $toolParameter->exclusiveMinimum);
        $this->assertSame($exclusiveMaximum, $toolParameter->exclusiveMaximum);
    }

    public function testInvalidExclusiveMaximumLessThanExclusiveMinimum(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(exclusiveMinimum: 10, exclusiveMaximum: 5);
    }

    public function testValidMinItemsAndMaxItems(): void
    {
        $minItems = 1;
        $maxItems = 5;
        $toolParameter = new With(minItems: $minItems, maxItems: $maxItems);
        $this->assertSame($minItems, $toolParameter->minItems);
        $this->assertSame($maxItems, $toolParameter->maxItems);
    }

    public function testInvalidMaxItemsLessThanMinItems(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(minItems: 5, maxItems: 1);
    }

    public function testValidUniqueItemsTrue(): void
    {
        $toolParameter = new With(uniqueItems: true);
        $this->assertTrue($toolParameter->uniqueItems);
    }

    public function testInvalidUniqueItemsFalse(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(uniqueItems: false);
    }

    public function testValidMinContainsAndMaxContains(): void
    {
        $minContains = 1;
        $maxContains = 3;
        $toolParameter = new With(minContains: $minContains, maxContains: $maxContains);
        $this->assertSame($minContains, $toolParameter->minContains);
        $this->assertSame($maxContains, $toolParameter->maxContains);
    }

    public function testInvalidMaxContainsLessThanMinContains(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(minContains: 3, maxContains: 1);
    }

    public function testValidRequired(): void
    {
        $toolParameter = new With(required: true);
        $this->assertTrue($toolParameter->required);
    }

    public function testValidMinPropertiesAndMaxProperties(): void
    {
        $minProperties = 1;
        $maxProperties = 5;
        $toolParameter = new With(minProperties: $minProperties, maxProperties: $maxProperties);
        $this->assertSame($minProperties, $toolParameter->minProperties);
        $this->assertSame($maxProperties, $toolParameter->maxProperties);
    }

    public function testInvalidMaxPropertiesLessThanMinProperties(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(minProperties: 5, maxProperties: 1);
    }

    public function testValidDependentRequired(): void
    {
        $toolParameter = new With(dependentRequired: true);
        $this->assertTrue($toolParameter->dependentRequired);
    }

    public function testValidCombination(): void
    {
        $toolParameter = new With(
            enum: ['value1', 'value2'],
            const: 'constant',
            pattern: '/^[a-z]+$/',
            minLength: 5,
            maxLength: 10,
            minimum: 0,
            maximum: 100,
            multipleOf: 5,
            exclusiveMinimum: 1,
            exclusiveMaximum: 99,
            minItems: 1,
            maxItems: 10,
            uniqueItems: true,
            minContains: 1,
            maxContains: 5,
            required: true,
            minProperties: 1,
            maxProperties: 5,
            dependentRequired: true
        );

        $this->assertInstanceOf(With::class, $toolParameter);
    }

    public function testInvalidCombination(): void
    {
        self::expectException(InvalidArgumentException::class);
        new With(minLength: -1, maxLength: -2);
    }
}
