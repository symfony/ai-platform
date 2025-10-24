<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\StructuredOutput;

use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\Result\ObjectResult;
use Symfony\AI\Platform\Result\RawResultInterface;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\AI\Platform\ResultConverterInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ResultConverter implements ResultConverterInterface
{
    public function __construct(
        private ResultConverterInterface $innerConverter,
        private SerializerInterface $serializer,
        private ?string $outputClass = null,
    ) {
    }

    public function supports(Model $model): bool
    {
        return true;
    }

    public function convert(RawResultInterface $result, array $options = []): ResultInterface
    {
        $innerResult = $this->innerConverter->convert($result, $options);

        if (!$innerResult instanceof TextResult) {
            return $innerResult;
        }

        $structure = null === $this->outputClass ? json_decode($innerResult->getContent(), true)
            : $this->serializer->deserialize($innerResult->getContent(), $this->outputClass, 'json');

        $objectResult = new ObjectResult($structure);
        $objectResult->setRawResult($result);
        $objectResult->getMetadata()->set($innerResult->getMetadata()->all());

        return $objectResult;
    }
}
