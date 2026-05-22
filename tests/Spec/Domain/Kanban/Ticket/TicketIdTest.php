<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Ticket;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\Ticket\TicketId;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TicketIdFixture;

#[CoversClass(TicketId::class)]
#[Small]
final class TicketIdTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringTicketId = TicketIdFixture::makeString();
        $ticketId = TicketId::fromString($stringTicketId);

        $this->assertInstanceOf(TicketId::class, $ticketId);
        $this->assertSame($stringTicketId, $ticketId->toString());
    }

    #[DataProvider('invalidTicketIdProvider')]
    #[TestDox('It fails when raw ticket id $scenario')]
    public function test_it_fails_when_raw_ticket_id_is_invalid(
        string $scenario,
        string $invalidTicketId,
    ): void {
        $this->expectException(ValidationFailedException::class);

        TicketId::fromString($invalidTicketId);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidTicketId: string,
     * }>
     */
    public static function invalidTicketIdProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty (``)',
            'invalidTicketId' => '',
        ];
    }
}
