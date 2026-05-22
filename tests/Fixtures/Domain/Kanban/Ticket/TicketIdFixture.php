<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket;

use Ssc\Dtk\Domain\Kanban\Ticket\TicketId;

final readonly class TicketIdFixture
{
    public static function make(): TicketId
    {
        return TicketId::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'PRJ-4423';
    }
}
