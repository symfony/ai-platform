<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Bridge\Ollama;

use Symfony\AI\Platform\Result\RawResultInterface;
use Symfony\AI\Platform\TokenUsage\TokenUsage;
use Symfony\AI\Platform\TokenUsage\TokenUsageExtractorInterface;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Guillaume Loulier <personal@guillaumeloulier.fr>
 */
final class TokenUsageExtractor implements TokenUsageExtractorInterface
{
    public function extract(RawResultInterface $rawResult, array $options = []): ?TokenUsageInterface
    {
        $response = $rawResult->getObject();
        if (!$response instanceof ResponseInterface) {
            return null;
        }

        if ($options['stream'] ?? false) {
            foreach ($rawResult->getDataStream() as $chunk) {
                if ($chunk['done']) {
                    return new TokenUsage(
                        $chunk['prompt_eval_count'],
                        $chunk['eval_count']
                    );
                }
            }

            return null;
        }

        $payload = $response->toArray();

        return new TokenUsage(
            $payload['prompt_eval_count'],
            $payload['eval_count']
        );
    }
}
