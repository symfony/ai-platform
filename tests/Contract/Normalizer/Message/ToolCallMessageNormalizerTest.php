<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Contract\Normalizer\Message;

use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Contract\Normalizer\Message\ToolCallMessageNormalizer;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\ToolCallMessage;
use Symfony\AI\Platform\Result\ToolCall;

final class ToolCallMessageNormalizerTest extends TestCase
{
    private ToolCallMessageNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ToolCallMessageNormalizer();
    }

    public function testSupportsNormalization()
    {
        $toolCallMessage = new ToolCallMessage(new ToolCall('id', 'function'), new Text('content'));

        $this->assertTrue($this->normalizer->supportsNormalization($toolCallMessage));
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testGetSupportedTypes()
    {
        $this->assertSame([ToolCallMessage::class => true], $this->normalizer->getSupportedTypes(null));
    }

    public function testNormalize()
    {
        $toolCall = new ToolCall('tool_call_123', 'get_weather', ['location' => 'Paris']);
        $message = new ToolCallMessage($toolCall, new Text('Weather data for Paris'));

        $expected = [
            'role' => 'tool',
            'content' => 'Weather data for Paris',
            'tool_call_id' => 'tool_call_123',
        ];

        $this->assertSame($expected, $this->normalizer->normalize($message));
    }

    public function testNormalizeMultimodalContentDegradesToText()
    {
        $toolCall = new ToolCall('tool_call_123', 'screenshot');
        $message = new ToolCallMessage($toolCall, new Text('Here it is'), new Image('binary', 'image/png'));

        $expected = [
            'role' => 'tool',
            'content' => 'Here it is',
            'tool_call_id' => 'tool_call_123',
        ];

        $this->assertSame($expected, $this->normalizer->normalize($message));
    }
}
