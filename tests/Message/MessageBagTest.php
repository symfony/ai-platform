<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Message;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\Content\ImageUrl;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\SystemMessage;
use Symfony\AI\Platform\Message\ToolCallMessage;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\Result\ToolCall;

#[CoversClass(MessageBag::class)]
#[UsesClass(Message::class)]
#[UsesClass(UserMessage::class)]
#[UsesClass(SystemMessage::class)]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(ImageUrl::class)]
#[UsesClass(Text::class)]
#[UsesClass(ToolCall::class)]
#[UsesClass(ToolCallMessage::class)]
#[Small]
final class MessageBagTest extends TestCase
{
    public function testGetSystemMessage()
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
            Message::ofToolCall(new ToolCall('tool', 'tool_name', ['param' => 'value']), 'Yes, go sleeping.'),
        );

        $systemMessage = $messageBag->getSystemMessage();

        $this->assertSame('My amazing system prompt.', $systemMessage->content);
    }

    public function testGetSystemMessageWithoutSystemMessage()
    {
        $messageBag = new MessageBag(
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
            Message::ofToolCall(new ToolCall('tool', 'tool_name', ['param' => 'value']), 'Yes, go sleeping.'),
        );

        $this->assertNull($messageBag->getSystemMessage());
    }

    public function testWith()
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $newMessage = Message::ofAssistant('It is time to wake up.');
        $newMessageBag = $messageBag->with($newMessage);

        $this->assertCount(3, $messageBag);
        $this->assertCount(4, $newMessageBag);

        $newMessageFromBag = $newMessageBag->getMessages()[3];

        $this->assertInstanceOf(AssistantMessage::class, $newMessageFromBag);
        $this->assertSame('It is time to wake up.', $newMessageFromBag->content);
    }

    public function testMerge()
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $messageBag = $messageBag->merge(new MessageBag(
            Message::ofAssistant('It is time to wake up.')
        ));

        $this->assertCount(4, $messageBag);

        $messageFromBag = $messageBag->getMessages()[3];

        $this->assertInstanceOf(AssistantMessage::class, $messageFromBag);
        $this->assertSame('It is time to wake up.', $messageFromBag->content);
    }

    public function testWithoutSystemMessage()
    {
        $messageBag = new MessageBag(
            Message::forSystem('My amazing system prompt.'),
            Message::ofAssistant('It is time to sleep.'),
            Message::forSystem('A system prompt in the middle.'),
            Message::ofUser('Hello, world!'),
            Message::forSystem('Another system prompt at the end'),
        );

        $newMessageBag = $messageBag->withoutSystemMessage();

        $this->assertCount(5, $messageBag);
        $this->assertCount(2, $newMessageBag);

        $assistantMessage = $newMessageBag->getMessages()[0];
        $this->assertInstanceOf(AssistantMessage::class, $assistantMessage);
        $this->assertSame('It is time to sleep.', $assistantMessage->content);

        $userMessage = $newMessageBag->getMessages()[1];
        $this->assertInstanceOf(UserMessage::class, $userMessage);
        $this->assertInstanceOf(Text::class, $userMessage->content[0]);
        $this->assertSame('Hello, world!', $userMessage->content[0]->text);
    }

    public function testPrepend()
    {
        $messageBag = new MessageBag(
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $newMessage = Message::forSystem('My amazing system prompt.');
        $newMessageBag = $messageBag->prepend($newMessage);

        $this->assertCount(2, $messageBag);
        $this->assertCount(3, $newMessageBag);

        $newMessageBagMessage = $newMessageBag->getMessages()[0];

        $this->assertInstanceOf(SystemMessage::class, $newMessageBagMessage);
        $this->assertSame('My amazing system prompt.', $newMessageBagMessage->content);
    }

    public function testContainsImageReturnsFalseWithoutImage()
    {
        $messageBag = new MessageBag(
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
        );

        $this->assertFalse($messageBag->containsImage());
    }

    public function testContainsImageReturnsTrueWithImage()
    {
        $messageBag = new MessageBag(
            Message::ofAssistant('It is time to sleep.'),
            Message::ofUser('Hello, world!'),
            Message::ofUser('My hint for how to analyze an image.', new ImageUrl('http://image-generator.local/my-fancy-image.png')),
        );

        $this->assertTrue($messageBag->containsImage());
    }
}
