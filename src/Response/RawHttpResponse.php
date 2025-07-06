<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Response;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de
 */
final readonly class RawHttpResponse implements RawResponseInterface
{
    public function __construct(
        private ResponseInterface $response,
    ) {
    }

    public function getRawData(): array
    {
        return $this->response->toArray(false);
    }

    public function getRawObject(): ResponseInterface
    {
        return $this->response;
    }
}
