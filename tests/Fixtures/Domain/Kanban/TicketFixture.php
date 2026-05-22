<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban;

use Ssc\Dtk\Domain\Kanban\Ticket;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TicketIdFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TitleFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TypeFixture;

final readonly class TicketFixture
{
    public static function make(): Ticket
    {
        return new Ticket(
            TicketIdFixture::make(),
            TitleFixture::make(),
            TypeFixture::make(),
        );
    }
}
