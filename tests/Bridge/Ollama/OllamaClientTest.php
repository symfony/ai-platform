<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Bridge\Ollama;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\Ollama\Ollama;
use Symfony\AI\Platform\Bridge\Ollama\OllamaClient;
use Symfony\AI\Platform\Model;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(OllamaClient::class)]
#[UsesClass(Ollama::class)]
#[UsesClass(Model::class)]
final class OllamaClientTest extends TestCase
{
    public function testSupportsModel()
    {
        $client = new OllamaClient(new MockHttpClient(), 'http://localhost:1234');

        $this->assertTrue($client->supports(new Ollama()));
        $this->assertFalse($client->supports(new Model('any-model')));
    }

    public function testOutputStructureIsSupported()
    {
        $httpClient = new MockHttpClient([
            new JsonMockResponse([
                'capabilities' => ['completion', 'tools'],
            ]),
            new JsonMockResponse([
                'model' => 'foo',
                'response' => [
                    'age' => 22,
                    'available' => true,
                ],
                'done' => true,
            ]),
        ], 'http://127.0.0.1:1234');

        $client = new OllamaClient($httpClient, 'http://127.0.0.1:1234');
        $response = $client->request(new Ollama(), [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Ollama is 22 years old and is busy saving the world. Respond using JSON',
                ],
            ],
            'model' => 'llama3.2',
        ], [
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'clock',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'age' => ['type' => 'integer'],
                            'available' => ['type' => 'boolean'],
                        ],
                        'required' => ['age', 'available'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ]);

        $this->assertSame(2, $httpClient->getRequestsCount());
        $this->assertSame([
            'model' => 'foo',
            'response' => [
                'age' => 22,
                'available' => true,
            ],
            'done' => true,
        ], $response->getData());
    }
}
