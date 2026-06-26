<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Message\Content;

/**
 * @phpstan-type FileSearchEntry array{
 *     file_id?: string,
 *     filename?: string,
 *     text?: string,
 *     score?: float,
 *     attributes?: array<string, mixed>,
 * }
 *
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class FileSearch implements ContentInterface
{
    /**
     * @param list<string>          $queries The search queries the model ran against the configured vector store(s)
     * @param list<FileSearchEntry> $results The matched file chunks, each with the source file id, filename, text snippet, relevance score and custom attributes
     * @param string|null           $id      Identifier of the file search call output item (e.g. "fs_...")
     * @param string|null           $status  Provider-reported status of the call, e.g. "completed", "searching", "incomplete" or "failed"
     */
    public function __construct(
        private readonly array $queries = [],
        private readonly array $results = [],
        private readonly ?string $id = null,
        private readonly ?string $status = null,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @return list<FileSearchEntry>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }
}
