<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tests\Job;

use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\Exception\UnexpectedResultTypeException;
use Symfony\AI\Platform\Job\JobClientInterface;
use Symfony\AI\Platform\Job\JobHandle;
use Symfony\AI\Platform\Job\JobRunner;
use Symfony\AI\Platform\Job\JobStateCase;
use Symfony\AI\Platform\Job\JobStatus;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Provider;
use Symfony\AI\Platform\Result\BinaryResult;
use Symfony\AI\Platform\Result\JobResult;
use Symfony\AI\Platform\Result\RawResultInterface;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\ResultConverterInterface;
use Symfony\AI\Platform\Test\MockModelCatalog;
use Symfony\AI\Platform\Test\MockModelClient;
use Symfony\AI\Platform\Test\MockResultConverter;
use Symfony\AI\Platform\TokenUsage\TokenUsageExtractorInterface;
use Symfony\Component\Clock\MockClock;

/**
 * The round trip that makes a job a job: start it, put the handle away, and resolve it later through
 * the bridge's job client - without the invocation that started it.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
final class JobResolutionTest extends TestCase
{
    public function testAStoredHandleResolvesThroughTheJobClientInAnotherProcess()
    {
        // Process one: start the job, keep nothing but the serialized handle.
        $stored = $this->platform($this->jobStartingConverter())->invoke('async-model', 'go')->asJob()->toString();

        // Process two: only the string survived, the job client is built from scratch.
        $handle = JobHandle::fromString($stored);
        $jobClient = $this->jobClient();

        $this->assertSame('jobs', $handle->getProvider());
        $this->assertTrue($jobClient->getStatus($handle)->is(JobStateCase::SUCCEEDED));
        $this->assertSame('FAKE_VIDEO', (new JobRunner(new MockClock()))->wait($jobClient, $handle)->asBinary());
    }

    public function testAskingForAJobOnASynchronousResultFails()
    {
        $this->expectException(UnexpectedResultTypeException::class);

        $this->platform(new MockResultConverter())->invoke('async-model', 'go')->asJob();
    }

    public function testReachingForThePayloadOfAJobFails()
    {
        $this->expectException(UnexpectedResultTypeException::class);

        $this->platform($this->jobStartingConverter())->invoke('async-model', 'go')->asBinary();
    }

    private function platform(ResultConverterInterface $converter): Platform
    {
        return new Platform([new Provider(
            'jobs',
            [new MockModelClient('accepted')],
            [$converter],
            new MockModelCatalog(['async-model' => ['class' => Model::class, 'capabilities' => [Capability::INPUT_TEXT]]]),
        )]);
    }

    /**
     * Stands in for a bridge converter whose provider answered with a task identifier instead of a
     * payload. Like a bridge, it creates a complete handle, provider name included.
     */
    private function jobStartingConverter(): ResultConverterInterface
    {
        return new class implements ResultConverterInterface {
            public function supports(Model $model): bool
            {
                return true;
            }

            public function convert(RawResultInterface $result, array $options = []): ResultInterface
            {
                return new JobResult(new JobHandle('task-1', ['mime_type' => 'video/mp4'], 'jobs'));
            }

            public function getTokenUsageExtractor(): ?TokenUsageExtractorInterface
            {
                return null;
            }
        };
    }

    private function jobClient(): JobClientInterface
    {
        return new class implements JobClientInterface {
            public function supports(JobHandle $handle): bool
            {
                return true;
            }

            public function getStatus(JobHandle $handle): JobStatus
            {
                return new JobStatus(JobStateCase::SUCCEEDED, 'Success');
            }

            public function getResult(JobHandle $handle): ResultInterface
            {
                return new BinaryResult('FAKE_VIDEO', $handle->get('mime_type'));
            }
        };
    }
}
