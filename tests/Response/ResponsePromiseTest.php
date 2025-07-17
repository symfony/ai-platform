<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Response\BaseResponse;
use Symfony\AI\Platform\Response\Exception\RawResponseAlreadySetException;
use Symfony\AI\Platform\Response\Metadata\Metadata;
use Symfony\AI\Platform\Response\RawHttpResponse;
use Symfony\AI\Platform\Response\RawResponseInterface;
use Symfony\AI\Platform\Response\ResponseInterface;
use Symfony\AI\Platform\Response\ResponsePromise;
use Symfony\AI\Platform\Response\TextResponse;
use Symfony\AI\Platform\ResponseConverterInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

#[CoversClass(ResponsePromise::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(TextResponse::class)]
#[UsesClass(RawResponseAlreadySetException::class)]
#[Small]
final class ResponsePromiseTest extends TestCase
{
    #[Test]
    public function itUnwrapsTheResponseWhenGettingContent(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);
        $rawHttpResponse = new RawHttpResponse($httpResponse);
        $textResponse = new TextResponse('test content');

        $responseConverter = self::createMock(ResponseConverterInterface::class);
        $responseConverter->expects(self::once())
            ->method('convert')
            ->with($rawHttpResponse, [])
            ->willReturn($textResponse);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), $rawHttpResponse);

        self::assertSame('test content', $responsePromise->getResponse()->getContent());
    }

    #[Test]
    public function itConvertsTheResponseOnlyOnce(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);
        $rawHttpResponse = new RawHttpResponse($httpResponse);
        $textResponse = new TextResponse('test content');

        $responseConverter = self::createMock(ResponseConverterInterface::class);
        $responseConverter->expects(self::once())
            ->method('convert')
            ->with($rawHttpResponse, [])
            ->willReturn($textResponse);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), $rawHttpResponse);

        // Call unwrap multiple times, but the converter should only be called once
        $responsePromise->await();
        $responsePromise->await();
        $responsePromise->getResponse();
    }

    #[Test]
    public function itGetsRawResponseDirectly(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);
        $responseConverter = $this->createStub(ResponseConverterInterface::class);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($httpResponse));

        self::assertSame($httpResponse, $responsePromise->getRawResponse()->getRawObject());
    }

    #[Test]
    public function itSetsRawResponseOnUnwrappedResponseWhenNeeded(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);

        $unwrappedResponse = $this->createResponse(null);

        $responseConverter = $this->createStub(ResponseConverterInterface::class);
        $responseConverter->method('convert')->willReturn($unwrappedResponse);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($httpResponse));
        $responsePromise->await();

        // The raw response in the model response is now set and not null anymore
        self::assertSame($httpResponse, $unwrappedResponse->getRawResponse()->getRawObject());
    }

    #[Test]
    public function itDoesNotSetRawResponseOnUnwrappedResponseWhenAlreadySet(): void
    {
        $originHttpResponse = $this->createStub(SymfonyHttpResponse::class);
        $anotherHttpResponse = $this->createStub(SymfonyHttpResponse::class);

        $unwrappedResponse = $this->createResponse($anotherHttpResponse);

        $responseConverter = $this->createStub(ResponseConverterInterface::class);
        $responseConverter->method('convert')->willReturn($unwrappedResponse);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($originHttpResponse));
        $responsePromise->await();

        // It is still the same raw response as set initially and so not overwritten
        self::assertSame($anotherHttpResponse, $unwrappedResponse->getRawResponse()->getRawObject());
    }

    #[Test]
    public function itPassesOptionsToConverter(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);
        $rawHttpResponse = new RawHttpResponse($httpResponse);
        $options = ['option1' => 'value1', 'option2' => 'value2'];

        $responseConverter = self::createMock(ResponseConverterInterface::class);
        $responseConverter->expects(self::once())
            ->method('convert')
            ->with($rawHttpResponse, $options)
            ->willReturn($this->createResponse(null));

        $responsePromise = new ResponsePromise($responseConverter->convert(...), $rawHttpResponse, $options);
        $responsePromise->await();
    }

    /**
     * Workaround for low deps because mocking the ResponseInterface leads to an exception with
     * mock creation "Type Traversable|object|array|string|null contains both object and a class type"
     * in PHPUnit MockClass.
     */
    private function createResponse(?SymfonyHttpResponse $httpResponse): ResponseInterface
    {
        $rawResponse = null !== $httpResponse ? new RawHttpResponse($httpResponse) : null;

        return new class($rawResponse) extends BaseResponse {
            public function __construct(protected ?RawResponseInterface $rawResponse)
            {
            }

            public function getContent(): string
            {
                return 'test content';
            }
        };
    }
}
