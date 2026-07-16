<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Test\Replay;

use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Base class for bridge replay tests.
 *
 * A replay test drives the real bridge pipeline
 * (Platform -> ModelClient -> RawHttpResult -> ResultConverter) against a
 * recorded {@see HttpCassette} instead of a live API, so a converter regression
 * (wrong deltas, wrong exception type, dropped content) surfaces as a failing
 * assertion. Cassettes are recorded against the real provider, or hand-seeded to
 * pin a specific response shape, and committed; replay runs on every CI build
 * with no credentials.
 *
 * Subclasses implement {@see createPlatform()} to build their bridge's platform
 * around the injected replay client, and {@see cassetteDirectory()} to point at
 * their committed cassettes. A missing cassette skips the test, mirroring the
 * capability-skip pattern of {@see \Symfony\AI\Store\Test\AbstractStoreIntegrationTestCase}.
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
abstract class AbstractBridgeReplayTestCase extends TestCase
{
    /**
     * Builds the bridge's platform with the replayed HTTP client injected.
     */
    abstract protected function createPlatform(HttpClientInterface $httpClient): PlatformInterface;

    /**
     * Absolute path to the directory holding this bridge's "<scenario>.json" cassettes.
     */
    abstract protected function cassetteDirectory(): string;

    /**
     * Returns a platform wired to replay the named cassette, skipping the test
     * when the bridge has not recorded that scenario.
     */
    protected function platformForCassette(string $scenario): PlatformInterface
    {
        $path = $this->cassetteDirectory().'/'.$scenario.'.json';

        if (!is_file($path)) {
            $this->markTestSkipped(\sprintf('No cassette recorded for scenario "%s".', $scenario));
        }

        return $this->createPlatform(new CassetteHttpClient(new HttpCassette($path), record: false));
    }
}
