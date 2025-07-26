<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Bridge\Ollama;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\Ollama\Ollama;
use Symfony\AI\Platform\Bridge\Ollama\OllamaResultConverter;
use Symfony\AI\Platform\Exception\RuntimeException;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\Result\InMemoryRawResult;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\AI\Platform\Result\ToolCall;
use Symfony\AI\Platform\Result\ToolCallResult;

#[CoversClass(OllamaResultConverter::class)]
#[Small]
#[UsesClass(Ollama::class)]
#[UsesClass(TextResult::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(ToolCallResult::class)]
final class OllamaResultConverterTest extends TestCase
{
    public function testSupportsLlamaModel()
    {
        $converter = new OllamaResultConverter();

        $this->assertTrue($converter->supports(new Ollama()));
        $this->assertFalse($converter->supports(new Model('any-model')));
    }

    public function testConvertTextResponse()
    {
        $converter = new OllamaResultConverter();
        $rawResult = new InMemoryRawResult([
            'message' => [
                'content' => 'Hello world',
            ],
        ]);

        $result = $converter->convert($rawResult);

        $this->assertInstanceOf(TextResult::class, $result);
        $this->assertSame('Hello world', $result->getContent());
    }

    public function testConvertToolCallResponse()
    {
        $converter = new OllamaResultConverter();
        $rawResult = new InMemoryRawResult([
            'message' => [
                'content' => 'This content will be ignored because tool_calls are present',
                'tool_calls' => [
                    [
                        'function' => [
                            'name' => 'test_function',
                            'arguments' => ['arg1' => 'value1'],
                        ],
                    ],
                ],
            ],
        ]);

        $result = $converter->convert($rawResult);

        $this->assertInstanceOf(ToolCallResult::class, $result);
        $toolCalls = $result->getContent();
        $this->assertCount(1, $toolCalls);
        $this->assertSame('0', $toolCalls[0]->id); // ID is the array index as a string
        $this->assertSame('test_function', $toolCalls[0]->name);
        $this->assertSame(['arg1' => 'value1'], $toolCalls[0]->arguments);
    }

    public function testConvertMultipleToolCallsResponse()
    {
        $converter = new OllamaResultConverter();
        $rawResult = new InMemoryRawResult([
            'message' => [
                'content' => 'This content will be ignored because tool_calls are present',
                'tool_calls' => [
                    [
                        'function' => [
                            'name' => 'function1',
                            'arguments' => ['param1' => 'value1'],
                        ],
                    ],
                    [
                        'function' => [
                            'name' => 'function2',
                            'arguments' => ['param2' => 'value2'],
                        ],
                    ],
                ],
            ],
        ]);

        $result = $converter->convert($rawResult);

        $this->assertInstanceOf(ToolCallResult::class, $result);
        $toolCalls = $result->getContent();
        $this->assertCount(2, $toolCalls);

        $this->assertSame('0', $toolCalls[0]->id);
        $this->assertSame('function1', $toolCalls[0]->name);
        $this->assertSame(['param1' => 'value1'], $toolCalls[0]->arguments);

        $this->assertSame('1', $toolCalls[1]->id);
        $this->assertSame('function2', $toolCalls[1]->name);
        $this->assertSame(['param2' => 'value2'], $toolCalls[1]->arguments);
    }

    public function testThrowsExceptionWhenNoMessage()
    {
        $converter = new OllamaResultConverter();
        $rawResult = new InMemoryRawResult([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Response does not contain message');

        $converter->convert($rawResult);
    }

    public function testThrowsExceptionWhenNoContent()
    {
        $converter = new OllamaResultConverter();
        $rawResult = new InMemoryRawResult([
            'message' => [],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Message does not contain content');

        $converter->convert($rawResult);
    }
}
