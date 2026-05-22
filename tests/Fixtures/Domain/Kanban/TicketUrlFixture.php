<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban;

use Ssc\Dtk\Domain\Kanban\TicketUrl;

final readonly class TicketUrlFixture
{
    public static function make(): TicketUrl
    {
        return TicketUrl::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'https://company.atlassian.net/browse/PRJ-4423';
    }
}
