<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Bridge\LMStudio\Embeddings;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\LMStudio\Embeddings;
use Symfony\AI\Platform\Bridge\LMStudio\Embeddings\ResponseConverter;
use Symfony\AI\Platform\Exception\RuntimeException;
use Symfony\AI\Platform\Response\VectorResponse;
use Symfony\AI\Platform\Vector\Vector;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(ResponseConverter::class)]
#[Small]
#[UsesClass(Vector::class)]
#[UsesClass(VectorResponse::class)]
#[UsesClass(Embeddings::class)]
class ResponseConverterTest extends TestCase
{
    #[Test]
    public function itConvertsAResponseToAVectorResponse(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('toArray')
            ->willReturn(
                json_decode(
                    <<<'JSON'
                        {
                          "object": "list",
                          "data": [
                            {
                              "object": "embedding",
                              "index": 0,
                              "embedding": [0.3, 0.4, 0.4]
                            },
                            {
                              "object": "embedding",
                              "index": 1,
                              "embedding": [0.0, 0.0, 0.2]
                            }
                          ]
                        }
                        JSON,
                    true
                )
            );

        $vectorResponse = (new ResponseConverter())->convert($response);
        $convertedContent = $vectorResponse->getContent();

        self::assertCount(2, $convertedContent);

        self::assertSame([0.3, 0.4, 0.4], $convertedContent[0]->getData());
        self::assertSame([0.0, 0.0, 0.2], $convertedContent[1]->getData());
    }

    #[Test]
    public function itThrowsExceptionWhenResponseDoesNotContainData(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('toArray')
            ->willReturn(['invalid' => 'response']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Response does not contain data');

        (new ResponseConverter())->convert($response);
    }

    #[Test]
    public function itSupportsEmbeddingsModel(): void
    {
        $converter = new ResponseConverter();

        self::assertTrue($converter->supports(new Embeddings('test-model')));
    }
}
