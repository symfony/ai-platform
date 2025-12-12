<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Bridge\VertexAi;

use Symfony\AI\Agent\Output;
use Symfony\AI\Agent\OutputProcessorInterface;
use Symfony\AI\Platform\Metadata\TokenUsage;
use Symfony\AI\Platform\Result\StreamResult;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Junaid Farooq <ulislam.junaid125@gmail.com>
 */
final class TokenOutputProcessor implements OutputProcessorInterface
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function processOutput(Output $output): void
    {
        $metadata = $output->getResult()->getMetadata();

        if ($output->getResult() instanceof StreamResult) {
            $lastChunk = null;

            foreach ($output->getResult()->getContent() as $chunk) {
                // Store last event that contains usage metadata
                if (isset($chunk['usageMetadata'])) {
                    $lastChunk = $chunk;
                }
            }

            if ($lastChunk) {
                $metadata->add('token_usage', $this->extractUsageMetadata($lastChunk['usageMetadata']));
            }

            return;
        }

        $rawResponse = $output->getResult()->getRawResult()?->getObject();
        if (!$rawResponse instanceof ResponseInterface) {
            return;
        }

        $content = $rawResponse->toArray(false);

        $metadata->add('token_usage', $this->extractUsageMetadata($content['usageMetadata'] ?? []));
    }

    /**
     * @param array{
     *     promptTokenCount?: int,
     *     candidatesTokenCount?: int,
     *     thoughtsTokenCount?: int,
     *     cachedContentTokenCount?: int,
     *     totalTokenCount?: int
     * } $usage
     */
    private function extractUsageMetadata(array $usage): TokenUsage
    {
        return new TokenUsage(
            promptTokens: $usage['promptTokenCount'] ?? null,
            completionTokens: $usage['candidatesTokenCount'] ?? null,
            thinkingTokens: $usage['thoughtsTokenCount'] ?? null,
            cachedTokens: $usage['cachedContentTokenCount'] ?? null,
            totalTokens: $usage['totalTokenCount'] ?? null,
        );
    }
}
