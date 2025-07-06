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

use Symfony\AI\Platform\Response\Exception\RawResponseAlreadySetException;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
trait RawResponseAwareTrait
{
    protected ?RawResponseInterface $rawResponse = null;

    public function setRawResponse(RawResponseInterface $rawResponse): void
    {
        if (isset($this->rawResponse)) {
            throw new RawResponseAlreadySetException();
        }

        $this->rawResponse = $rawResponse;
    }

    public function getRawResponse(): ?RawResponseInterface
    {
        return $this->rawResponse;
    }
}
