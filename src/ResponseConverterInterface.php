<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform;

use Symfony\AI\Platform\Response\RawResponseInterface;
use Symfony\AI\Platform\Response\ResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface ResponseConverterInterface
{
    public function supports(Model $model): bool;

    /**
     * @param array<string, mixed> $options
     */
    public function convert(RawResponseInterface $response, array $options = []): ResponseInterface;
}
