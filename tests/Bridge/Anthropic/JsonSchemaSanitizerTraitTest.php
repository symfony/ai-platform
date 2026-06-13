<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Bridge\Anthropic;

use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\Anthropic\JsonSchemaSanitizerTrait;

/**
 * @author Valtteri R <valtzu@gmail.com>
 */
final class JsonSchemaSanitizerTraitTest extends TestCase
{
    private object $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new class {
            use JsonSchemaSanitizerTrait { normalizeStructuredOutputSchema as public; }
        };
    }

    public function testUnsupportedConstraintsAreMovedToDescription()
    {
        $schema = [
            'type' => 'integer',
            'minimum' => 1,
            'maximum' => 100,
            'multipleOf' => 5,
        ];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertArrayNotHasKey('minimum', $result);
        $this->assertArrayNotHasKey('maximum', $result);
        $this->assertArrayNotHasKey('multipleOf', $result);
        $this->assertSame('{"minimum":1,"maximum":100,"multipleOf":5}', $result['description']);
    }

    public function testUnsupportedConstraintsAreAppendedToExistingDescription()
    {
        $schema = [
            'type' => 'string',
            'description' => 'A short label',
            'minLength' => 2,
            'maxLength' => 50,
        ];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertArrayNotHasKey('minLength', $result);
        $this->assertArrayNotHasKey('maxLength', $result);
        $this->assertSame("A short label\n\n{\"minLength\":2,\"maxLength\":50}", $result['description']);
    }

    public function testMinItemsGreaterThanOneIsMovedToDescription()
    {
        $schema = ['type' => 'array', 'minItems' => 3];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertArrayNotHasKey('minItems', $result);
        $this->assertSame('{"minItems":3}', $result['description']);
    }

    public function testMinItemsOneIsKept()
    {
        $schema = ['type' => 'array', 'minItems' => 1];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertSame(1, $result['minItems']);
        $this->assertArrayNotHasKey('description', $result);
    }

    public function testAdditionalPropertiesTrueIsMovedToDescription()
    {
        $schema = ['type' => 'object', 'additionalProperties' => true];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertArrayNotHasKey('additionalProperties', $result);
        $this->assertSame('{"additionalProperties":true}', $result['description']);
    }

    public function testAdditionalPropertiesFalseIsKept()
    {
        $schema = ['type' => 'object', 'additionalProperties' => false];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertFalse($result['additionalProperties']);
        $this->assertArrayNotHasKey('description', $result);
    }

    public function testExternalRefIsMovedToDescription()
    {
        $schema = ['$ref' => 'https://example.com/schema.json'];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertArrayNotHasKey('$ref', $result);
        $this->assertSame('{"$ref":"https://example.com/schema.json"}', $result['description']);
    }

    public function testLocalRefIsKept()
    {
        $schema = ['$ref' => '#/definitions/Foo'];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertSame('#/definitions/Foo', $result['$ref']);
        $this->assertArrayNotHasKey('description', $result);
    }

    public function testNestedPropertiesAreSanitizedRecursively()
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'age' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 150],
            ],
        ];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $age = $result['properties']['age'];
        $this->assertArrayNotHasKey('minimum', $age);
        $this->assertArrayNotHasKey('maximum', $age);
        $this->assertSame('{"minimum":0,"maximum":150}', $age['description']);
    }

    public function testSupportedPropertiesAreNotTouched()
    {
        $schema = [
            'type' => 'object',
            'description' => 'A person',
            'properties' => [
                'name' => ['type' => 'string'],
            ],
            'required' => ['name'],
            'additionalProperties' => false,
            'minItems' => 0,
        ];

        $result = $this->sanitizer->normalizeStructuredOutputSchema($schema);

        $this->assertSame('A person', $result['description']);
        $this->assertFalse($result['additionalProperties']);
        $this->assertSame(0, $result['minItems']);
    }
}
