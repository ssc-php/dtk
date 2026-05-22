<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Kanban\Ticket;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TicketIdFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TitleFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TypeFixture;

#[CoversClass(Ticket::class)]
#[Small]
final class TicketTest extends TestCase
{
    #[TestDox('It has TicketId')]
    public function test_it_has_ticket_id(): void
    {
        $id = TicketIdFixture::make();
        $ticket = new Ticket(
            $id,
            TitleFixture::make(),
            TypeFixture::make(),
        );

        $this->assertSame($id, $ticket->ticketId);
    }

    #[TestDox('It has Title')]
    public function test_it_has_title(): void
    {
        $title = TitleFixture::make();
        $ticket = new Ticket(
            TicketIdFixture::make(),
            $title,
            TypeFixture::make(),
        );

        $this->assertSame($title, $ticket->title);
    }

    #[TestDox('It has Type')]
    public function test_it_has_type(): void
    {
        $type = TypeFixture::make();
        $ticket = new Ticket(
            TicketIdFixture::make(),
            TitleFixture::make(),
            $type,
        );

        $this->assertSame($type, $ticket->type);
    }
}
