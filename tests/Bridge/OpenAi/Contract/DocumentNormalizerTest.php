<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Bridge\OpenAi\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\Gemini\Contract\MessageBagNormalizer;
use Symfony\AI\Platform\Bridge\OpenAi\Contract\FileNormalizer;
use Symfony\AI\Platform\Bridge\OpenAi\Gpt;
use Symfony\AI\Platform\Contract;
use Symfony\AI\Platform\Message\Content\Document;
use Symfony\AI\Platform\Message\Content\File;

#[Medium]
#[CoversClass(FileNormalizer::class)]
#[CoversClass(MessageBagNormalizer::class)]
final class DocumentNormalizerTest extends TestCase
{
    public function testSupportsNormalization()
    {
        $normalizer = new FileNormalizer();

        $this->assertTrue($normalizer->supportsNormalization(new Document('some content', 'application/pdf'), context: [
            Contract::CONTEXT_MODEL => new Gpt(),
        ]));
        $this->assertTrue($normalizer->supportsNormalization(new File('some content', 'application/pdf'), context: [
            Contract::CONTEXT_MODEL => new Gpt(),
        ]));
        $this->assertFalse($normalizer->supportsNormalization('not a document'));
    }

    public function testGetSupportedTypes()
    {
        $normalizer = new FileNormalizer();

        $expected = [
            File::class => true,
        ];

        $this->assertSame($expected, $normalizer->getSupportedTypes(null));
    }

    #[DataProvider('normalizeDataProvider')]
    public function testNormalize(File $file, array $expected)
    {
        $normalizer = new FileNormalizer();

        $normalized = $normalizer->normalize($file);

        $this->assertEquals($expected, $normalized);
    }

    public static function normalizeDataProvider(): iterable
    {
        yield 'document from file' => [
            File::fromFile(\dirname(__DIR__, 6).'/fixtures/document.pdf'),
            [
                'type' => 'file',
                'file' => [
                    'filename' => 'document.pdf',
                    'file_data' => 'data:application/pdf;base64,'.base64_encode(file_get_contents(\dirname(__DIR__, 6).'/fixtures/document.pdf')),
                ],
            ],
        ];
    }
}
