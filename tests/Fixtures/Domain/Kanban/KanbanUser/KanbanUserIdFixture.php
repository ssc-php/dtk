<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\KanbanUser;

use Ssc\Dtk\Domain\Kanban\KanbanUser\KanbanUserId;

final readonly class KanbanUserIdFixture
{
    public static function make(): KanbanUserId
    {
        return KanbanUserId::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return '5b10a2844c20165700ede21g';
    }
}
