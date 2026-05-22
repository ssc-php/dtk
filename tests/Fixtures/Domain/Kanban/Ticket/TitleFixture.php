<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket;

use Ssc\Dtk\Domain\Kanban\Ticket\Title;

final readonly class TitleFixture
{
    public static function make(): Title
    {
        return Title::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'Fix broken login';
    }
}
