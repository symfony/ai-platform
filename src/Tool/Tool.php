<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Tool;

use Symfony\AI\Platform\Contract\JsonSchema\Factory;

/**
 * @phpstan-import-type JsonSchema from Factory
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class Tool
{
    /**
     * @param JsonSchema|null $parameters
     */
    public function __construct(
        private ExecutionReference $reference,
        private string $name,
        private string $description,
        private ?array $parameters = null,
    ) {
    }

    public function getReference(): ExecutionReference
    {
        return $this->reference;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return JsonSchema|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }
}
