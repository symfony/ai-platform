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

use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\Event\InvocationEvent;
use Symfony\AI\Platform\Event\ResultEvent;
use Symfony\AI\Platform\Exception\InvalidArgumentException;
use Symfony\AI\Platform\Exception\MissingModelSupportException;
use Symfony\AI\Platform\Result\DeferredResult;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class PlatformSubscriber implements EventSubscriberInterface
{
    private string $outputStructure;

    public function __construct(
        private readonly ResponseFormatFactoryInterface $responseFormatFactory = new ResponseFormatFactory(),
        private ?SerializerInterface $serializer = null,
    ) {
        if (null !== $this->serializer) {
            return;
        }

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);
        $propertyInfo = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);

        $normalizers = [
            new BackedEnumNormalizer(),
            new ObjectNormalizer(
                classMetadataFactory: $classMetadataFactory,
                propertyTypeExtractor: $propertyInfo,
                classDiscriminatorResolver: $discriminator,
            ),
            new ArrayDenormalizer(),
        ];

        $this->serializer = new Serializer($normalizers, [new JsonEncoder()]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InvocationEvent::class => 'processInput',
            ResultEvent::class => 'processResult',
        ];
    }

    /**
     * @throws MissingModelSupportException When structured output is requested but the model doesn't support it
     * @throws InvalidArgumentException     When streaming is enabled with structured output (incompatible options)
     */
    public function processInput(InvocationEvent $event): void
    {
        $options = $event->getOptions();

        if (!isset($options['output_structure'])) {
            return;
        }

        if (!$event->getModel()->supports(Capability::OUTPUT_STRUCTURED)) {
            throw MissingModelSupportException::forStructuredOutput($event->getModel()::class);
        }

        if (true === ($options['stream'] ?? false)) {
            throw new InvalidArgumentException('Streamed responses are not supported for structured output.');
        }

        $options['response_format'] = $this->responseFormatFactory->create($options['output_structure']);

        $this->outputStructure = $options['output_structure'];
        unset($options['output_structure']);

        $event->setOptions($options);
    }

    public function processResult(ResultEvent $event): void
    {
        $options = $event->getOptions();

        if (!isset($options['response_format'])) {
            return;
        }

        $deferred = $event->getDeferredResult();
        $converter = new ResultConverter($deferred->getResultConverter(), $this->serializer, $this->outputStructure ?? null);

        $event->setDeferredResult(new DeferredResult($converter, $deferred->getRawResult(), $options));
    }
}
