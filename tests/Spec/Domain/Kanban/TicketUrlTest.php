<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\TicketUrl;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\TicketUrlFixture;

#[CoversClass(TicketUrl::class)]
#[Small]
final class TicketUrlTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringTicketUrl = TicketUrlFixture::makeString();
        $kanbanTicketUrl = TicketUrl::fromString($stringTicketUrl);

        $this->assertInstanceOf(TicketUrl::class, $kanbanTicketUrl);
        $this->assertSame($stringTicketUrl, $kanbanTicketUrl->toString());
    }

    #[DataProvider('invalidTicketUrlProvider')]
    #[TestDox('It fails when: raw kanban ticket URL $scenario')]
    public function test_it_fails_when_raw_kanban_ticket_url_is_invalid(
        string $scenario,
        string $invalidTicketUrl,
    ): void {
        $this->expectException(ValidationFailedException::class);

        TicketUrl::fromString($invalidTicketUrl);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidTicketUrl: string,
     * }>
     */
    public static function invalidTicketUrlProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty (``)',
            'invalidTicketUrl' => '',
        ];
        $url = 'company.atlassian.net/browse/PRJ-4423';
        yield [
            'scenario' => "has no scheme (`{$url}`)",
            'invalidTicketUrl' => $url,
        ];
        $url = 'ftp://company.atlassian.net/browse/PRJ-4423';
        yield [
            'scenario' => "has wrong scheme (`{$url}`)",
            'invalidTicketUrl' => $url,
        ];
        $url = 'https://company.atlassian.net';
        yield [
            'scenario' => "has no path (`{$url}`)",
            'invalidTicketUrl' => $url,
        ];
    }
}
