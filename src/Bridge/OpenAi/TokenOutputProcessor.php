<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Bridge\OpenAi;

use Symfony\AI\Agent\Output;
use Symfony\AI\Agent\OutputProcessorInterface;
use Symfony\AI\Platform\Metadata\TokenUsage;
use Symfony\AI\Platform\Result\StreamResult;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final class TokenOutputProcessor implements OutputProcessorInterface
{
    public function processOutput(Output $output): void
    {
        if ($output->getResult() instanceof StreamResult) {
            // Streams have to be handled manually as the tokens are part of the streamed chunks
            return;
        }

        $rawResponse = $output->getResult()->getRawResult()?->getObject();
        if (!$rawResponse instanceof ResponseInterface) {
            return;
        }

        $metadata = $output->getResult()->getMetadata();
        $content = $rawResponse->toArray(false);

        $remainingTokens = $rawResponse->getHeaders(false)['x-ratelimit-remaining-tokens'][0] ?? null;
        $tokenUsage = new TokenUsage(
            promptTokens: $content['usage']['prompt_tokens'] ?? null,
            completionTokens: $content['usage']['completion_tokens'] ?? null,
            thinkingTokens: $content['usage']['completion_tokens_details']['reasoning_tokens'] ?? null,
            cachedTokens: $content['usage']['prompt_tokens_details']['cached_tokens'] ?? null,
            remainingTokens: null !== $remainingTokens ? (int) $remainingTokens : null,
            totalTokens: $content['usage']['total_tokens'] ?? null,
        );

        $metadata->add('token_usage', $tokenUsage);
    }
}
