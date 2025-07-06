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

/**
 * Base class for raw model responses.
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface RawResponseInterface
{
    /**
     * Returns an array representation of the raw response data.
     *
     * @return array<string, mixed>
     */
    public function getRawData(): array;

    public function getRawObject(): object;
}
