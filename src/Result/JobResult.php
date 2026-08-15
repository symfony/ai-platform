<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Result;

use Symfony\AI\Platform\Job\JobHandle;

/**
 * The provider accepted the work but has not done it yet.
 *
 * Returned by bridges whose backend answers a request with a job identifier instead of a result -
 * video generation, asynchronous speech synthesis, batch endpoints. The content is the
 * {@see JobHandle} needed to come back to that job, possibly from another process.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
final class JobResult extends BaseResult
{
    public function __construct(
        private readonly JobHandle $handle,
    ) {
    }

    public function getContent(): JobHandle
    {
        return $this->handle;
    }
}
