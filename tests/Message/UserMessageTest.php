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
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\TimeBasedUidInterface;
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

        $this->assertSame(Role::User, $obj->getRole());
        $this->assertCount(1, $obj->content);
        $this->assertInstanceOf(Text::class, $obj->content[0]);
        $this->assertSame('foo', $obj->content[0]->text);
    }

    #[Test]
    public function constructionIsPossibleWithMultipleContent(): void
    {
        $message = new UserMessage(new Text('foo'), new ImageUrl('https://foo.com/bar.jpg'));

        $this->assertCount(2, $message->content);
    }

    #[Test]
    public function hasAudioContentWithoutAudio(): void
    {
        $message = new UserMessage(new Text('foo'), new Text('bar'));

        $this->assertFalse($message->hasAudioContent());
    }

    #[Test]
    public function hasAudioContentWithAudio(): void
    {
        $message = new UserMessage(new Text('foo'), Audio::fromFile(\dirname(__DIR__, 4).'/fixtures/audio.mp3'));

        $this->assertTrue($message->hasAudioContent());
    }

    #[Test]
    public function hasImageContentWithoutImage(): void
    {
        $message = new UserMessage(new Text('foo'), new Text('bar'));

        $this->assertFalse($message->hasImageContent());
    }

    #[Test]
    public function hasImageContentWithImage(): void
    {
        $message = new UserMessage(new Text('foo'), new ImageUrl('https://foo.com/bar.jpg'));

        $this->assertTrue($message->hasImageContent());
    }

    #[Test]
    public function messageHasUid(): void
    {
        $message = new UserMessage(new Text('foo'));

        $this->assertInstanceOf(UuidV7::class, $message->id);
        $this->assertInstanceOf(UuidV7::class, $message->getId());
        $this->assertSame($message->id, $message->getId());
    }

    #[Test]
    public function differentMessagesHaveDifferentUids(): void
    {
        $message1 = new UserMessage(new Text('foo'));
        $message2 = new UserMessage(new Text('bar'));

        $this->assertNotSame($message1->getId()->toRfc4122(), $message2->getId()->toRfc4122());
        self::assertIsUuidV7($message1->getId()->toRfc4122());
        self::assertIsUuidV7($message2->getId()->toRfc4122());
    }

    #[Test]
    public function sameMessagesHaveDifferentUids(): void
    {
        $message1 = new UserMessage(new Text('foo'));
        $message2 = new UserMessage(new Text('foo'));

        $this->assertNotSame($message1->getId()->toRfc4122(), $message2->getId()->toRfc4122());
        self::assertIsUuidV7($message1->getId()->toRfc4122());
        self::assertIsUuidV7($message2->getId()->toRfc4122());
    }

    #[Test]
    public function messageIdImplementsRequiredInterfaces(): void
    {
        $message = new UserMessage(new Text('test'));

        $this->assertInstanceOf(AbstractUid::class, $message->getId());
        $this->assertInstanceOf(TimeBasedUidInterface::class, $message->getId());
        $this->assertInstanceOf(UuidV7::class, $message->getId());
    }
}
