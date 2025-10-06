<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Bridge\Azure\OpenAi;

use Symfony\AI\Platform\Bridge\Azure\OpenAi\ModelCatalog;
use Symfony\AI\Platform\Bridge\OpenAi\Embeddings;
use Symfony\AI\Platform\Bridge\OpenAi\Gpt;
use Symfony\AI\Platform\Bridge\OpenAi\Whisper;
use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\ModelCatalog\ModelCatalogInterface;
use Symfony\AI\Platform\Test\ModelCatalogTestCase;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class ModelCatalogTest extends ModelCatalogTestCase
{
    public static function modelsProvider(): iterable
    {
        yield 'gpt-4o' => ['gpt-4o', Gpt::class, [Capability::INPUT_TEXT, Capability::INPUT_IMAGE, Capability::INPUT_AUDIO, Capability::OUTPUT_TEXT, Capability::OUTPUT_AUDIO, Capability::OUTPUT_STREAMING, Capability::TOOL_CALLING]];
        yield 'gpt-4o-mini' => ['gpt-4o-mini', Gpt::class, [Capability::INPUT_TEXT, Capability::INPUT_IMAGE, Capability::OUTPUT_TEXT, Capability::OUTPUT_STREAMING, Capability::TOOL_CALLING]];
        yield 'gpt-4-turbo' => ['gpt-4-turbo', Gpt::class, [Capability::INPUT_TEXT, Capability::INPUT_IMAGE, Capability::OUTPUT_TEXT, Capability::OUTPUT_STREAMING, Capability::TOOL_CALLING]];
        yield 'gpt-4' => ['gpt-4', Gpt::class, [Capability::INPUT_TEXT, Capability::OUTPUT_TEXT, Capability::OUTPUT_STREAMING, Capability::TOOL_CALLING]];
        yield 'gpt-35-turbo' => ['gpt-35-turbo', Gpt::class, [Capability::INPUT_TEXT, Capability::OUTPUT_TEXT, Capability::OUTPUT_STREAMING, Capability::TOOL_CALLING]];
        yield 'whisper' => ['whisper', Whisper::class, [Capability::INPUT_AUDIO, Capability::OUTPUT_TEXT, Capability::SPEECH_TO_TEXT]];
        yield 'whisper-1' => ['whisper-1', Whisper::class, [Capability::INPUT_AUDIO, Capability::OUTPUT_TEXT, Capability::SPEECH_TO_TEXT]];
        yield 'text-embedding-ada-002' => ['text-embedding-ada-002', Embeddings::class, [Capability::INPUT_TEXT, Capability::OUTPUT_STRUCTURED]];
        yield 'text-embedding-3-small' => ['text-embedding-3-small', Embeddings::class, [Capability::INPUT_TEXT, Capability::OUTPUT_STRUCTURED]];
        yield 'text-embedding-3-large' => ['text-embedding-3-large', Embeddings::class, [Capability::INPUT_TEXT, Capability::OUTPUT_STRUCTURED]];
    }

    protected function createModelCatalog(): ModelCatalogInterface
    {
        return new ModelCatalog();
    }
}
