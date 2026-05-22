<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket;

use Ssc\Dtk\Domain\Kanban\Ticket\Type;

final readonly class TypeFixture
{
    public static function make(): Type
    {
        return Type::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'Bug';
    }
}
