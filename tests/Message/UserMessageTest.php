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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Message\Content\Audio;
use Symfony\AI\Platform\Message\Content\ImageUrl;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\Role;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\Tests\Helper\UuidAssertionTrait;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(UserMessage::class)]
#[UsesClass(Text::class)]
#[UsesClass(Audio::class)]
#[UsesClass(ImageUrl::class)]
#[UsesClass(Role::class)]
#[Small]
final class UserMessageTest extends TestCase
{
    use UuidAssertionTrait;

    #[Test]
    public function constructionIsPossible(): void
    {
        $obj = new UserMessage(new Text('foo'));

        self::assertSame(Role::User, $obj->getRole());
        self::assertCount(1, $obj->content);
        self::assertInstanceOf(Text::class, $obj->content[0]);
        self::assertSame('foo', $obj->content[0]->text);
    }

    #[Test]
    public function constructionIsPossibleWithMultipleContent(): void
    {
        $message = new UserMessage(new Text('foo'), new ImageUrl('https://foo.com/bar.jpg'));

        self::assertCount(2, $message->content);
    }

    #[Test]
    public function hasAudioContentWithoutAudio(): void
    {
        $message = new UserMessage(new Text('foo'), new Text('bar'));

        self::assertFalse($message->hasAudioContent());
    }

    #[Test]
    public function hasAudioContentWithAudio(): void
    {
        $message = new UserMessage(new Text('foo'), Audio::fromFile(\dirname(__DIR__, 4).'/fixtures/audio.mp3'));

        self::assertTrue($message->hasAudioContent());
    }

    #[Test]
    public function hasImageContentWithoutImage(): void
    {
        $message = new UserMessage(new Text('foo'), new Text('bar'));

        self::assertFalse($message->hasImageContent());
    }

    #[Test]
    public function hasImageContentWithImage(): void
    {
        $message = new UserMessage(new Text('foo'), new ImageUrl('https://foo.com/bar.jpg'));

        self::assertTrue($message->hasImageContent());
    }

    #[Test]
    public function messageHasUid(): void
    {
        $message = new UserMessage(new Text('foo'));

        self::assertInstanceOf(UuidV7::class, $message->id);
        self::assertInstanceOf(UuidV7::class, $message->getId());
        self::assertSame($message->id, $message->getId());
    }

    #[Test]
    public function differentMessagesHaveDifferentUids(): void
    {
        $message1 = new UserMessage(new Text('foo'));
        $message2 = new UserMessage(new Text('bar'));

        self::assertNotSame($message1->getId()->toRfc4122(), $message2->getId()->toRfc4122());
        self::assertIsUuidV7($message1->getId()->toRfc4122());
        self::assertIsUuidV7($message2->getId()->toRfc4122());
    }

    #[Test]
    public function sameMessagesHaveDifferentUids(): void
    {
        $message1 = new UserMessage(new Text('foo'));
        $message2 = new UserMessage(new Text('foo'));

        self::assertNotSame($message1->getId()->toRfc4122(), $message2->getId()->toRfc4122());
        self::assertIsUuidV7($message1->getId()->toRfc4122());
        self::assertIsUuidV7($message2->getId()->toRfc4122());
    }
}
