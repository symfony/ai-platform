<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Message;

use Symfony\AI\Platform\Response\ToolCall;
use Symfony\Component\Uid\Uuid;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class ToolCallMessage implements MessageInterface
{
    public Uuid $id;

    public function __construct(
        public ToolCall $toolCall,
        public string $content,
    ) {
        $this->id = Uuid::v7();
    }

    public function getRole(): Role
    {
        return Role::ToolCall;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}
