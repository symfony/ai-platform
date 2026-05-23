<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\StructuredOutput\Validator;

use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Event\ResultEvent;
use Symfony\AI\Platform\Exception\ValidationException;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\PlainConverter;
use Symfony\AI\Platform\Result\DeferredResult;
use Symfony\AI\Platform\Result\InMemoryRawResult;
use Symfony\AI\Platform\Result\ObjectResult;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\StreamResult;
use Symfony\AI\Platform\ResultConverterInterface;
use Symfony\AI\Platform\StructuredOutput\PlatformSubscriber;
use Symfony\AI\Platform\StructuredOutput\Serializer;
use Symfony\AI\Platform\StructuredOutput\Streaming\PartialObjectStreamListener;
use Symfony\AI\Platform\StructuredOutput\Validator\ValidatorResultConverter;
use Symfony\AI\Platform\StructuredOutput\Validator\ValidatorSubscriber;
use Symfony\AI\Platform\Tests\Fixtures\StructuredOutput\UserWithConstraints;

final class ValidatorSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            ResultEvent::class => ['processResult', -10],
        ], ValidatorSubscriber::getSubscribedEvents());
    }

    public function testProcessResultWithResponseFormat()
    {
        $subscriber = new ValidatorSubscriber();

        $model = new Model('gpt-4');

        $object = new UserWithConstraints();

        $converter = $this->createStub(ResultConverterInterface::class);
        $converter->method('supports')->willReturn(true);
        $converter->method('convert')->willReturn(new ObjectResult($object));

        $options = [PlatformSubscriber::RESPONSE_FORMAT => 'SomeClass'];
        $deferred = new DeferredResult($converter, new InMemoryRawResult(), $options);
        $event = new ResultEvent($model, $deferred, $options);

        $subscriber->processResult($event);

        $newDeferred = $event->getDeferredResult();
        $this->assertInstanceOf(ValidatorResultConverter::class, $newDeferred->getResultConverter());

        $this->expectException(ValidationException::class);
        $event->getDeferredResult()->asObject();
    }

    public function testIgnoreValidationWhenNoResponseFormatSet()
    {
        $subscriber = new ValidatorSubscriber();

        $model = new Model('gpt-4');

        $object = new UserWithConstraints();

        $converter = $this->createStub(ResultConverterInterface::class);
        $converter->method('supports')->willReturn(true);
        $converter->method('convert')->willReturn(new ObjectResult($object));

        $options = [];
        $deferred = new DeferredResult($converter, new InMemoryRawResult(), $options);
        $event = new ResultEvent($model, $deferred, $options);

        $subscriber->processResult($event);

        $newDeferred = $event->getDeferredResult();
        $this->assertSame($converter, $newDeferred->getResultConverter());
        $this->assertSame($object, $event->getDeferredResult()->asObject());
    }

    public function testStreamingPartialsAreNotValidated()
    {
        $stream = $this->buildStreamWithListener([
            '{"id":1,"name":"',  // partial: name still empty → would violate NotBlank
            'Alice"}',
        ]);

        // Wrap the converter chain like the real subscribers do.
        $deferred = new DeferredResult(
            new ValidatorResultConverter(new PlainConverter($stream), $this->createValidator()),
            new InMemoryRawResult(),
            [PlatformSubscriber::RESPONSE_FORMAT => UserWithConstraints::class],
        );

        // Trigger converter chain (which injects the validator into the listener).
        $deferred->getResult();

        $partials = iterator_to_array($deferred->asStreamedObject(), false);

        // Partials must be emitted regardless of whether they would individually fail validation.
        $this->assertNotEmpty($partials);
        foreach ($partials as $object) {
            $this->assertInstanceOf(UserWithConstraints::class, $object);
        }
    }

    public function testStreamingFinalObjectIsValidated()
    {
        $stream = $this->buildStreamWithListener([
            '{"id":1,"name":""}',  // final violates NotBlank
        ]);

        $deferred = new DeferredResult(
            new ValidatorResultConverter(new PlainConverter($stream), $this->createValidator()),
            new InMemoryRawResult(),
            [PlatformSubscriber::RESPONSE_FORMAT => UserWithConstraints::class],
        );

        $deferred->getResult();

        $this->expectException(ValidationException::class);

        $deferred->asObject();
    }

    /**
     * @param string[] $chunks
     */
    private function buildStreamWithListener(array $chunks): StreamResult
    {
        $generator = (static function () use ($chunks): \Generator {
            foreach ($chunks as $chunk) {
                yield new TextDelta($chunk);
            }
        })();

        return new StreamResult(
            $generator,
            [new PartialObjectStreamListener(new Serializer(), UserWithConstraints::class)],
        );
    }

    private function createValidator(): \Symfony\Component\Validator\Validator\ValidatorInterface
    {
        return \Symfony\Component\Validator\Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
