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

use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\TimeBasedUidInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class SystemMessage implements MessageInterface
{
    public AbstractUid&TimeBasedUidInterface $id;

    public function __construct(public string $content)
    {
        $this->id = Uuid::v7();
    }

    public function getRole(): Role
    {
        return Role::System;
    }

    public function getId(): AbstractUid&TimeBasedUidInterface
    {
        return $this->id;
    }
}
