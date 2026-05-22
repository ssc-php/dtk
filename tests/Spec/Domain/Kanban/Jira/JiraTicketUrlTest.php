<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTicketUrl;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\BaseUrlFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTicketUrlFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket\TicketIdFixture;

#[CoversClass(JiraTicketUrl::class)]
#[Small]
final class JiraTicketUrlTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringJiraTicketUrl = JiraTicketUrlFixture::makeString();
        $jiraTicketUrl = JiraTicketUrl::fromString($stringJiraTicketUrl);

        $this->assertInstanceOf(JiraTicketUrl::class, $jiraTicketUrl);
        $this->assertSame($stringJiraTicketUrl, $jiraTicketUrl->toString());
    }

    #[TestDox('It can be converted to BaseUrl')]
    public function test_it_can_be_converted_to_base_url(): void
    {
        $jiraTicketUrl = JiraTicketUrlFixture::make();

        $this->assertSame(BaseUrlFixture::makeString(), $jiraTicketUrl->toBaseUrl()->toString());
    }

    #[TestDox('It can be converted to TicketId')]
    public function test_it_can_be_converted_to_ticket_id(): void
    {
        $jiraTicketUrl = JiraTicketUrlFixture::make();

        $this->assertSame(TicketIdFixture::makeString(), $jiraTicketUrl->toTicketId()->toString());
    }

    #[DataProvider('invalidJiraTicketUrlProvider')]
    #[TestDox('It fails when: raw Jira ticket URL $scenario')]
    public function test_it_fails_when_raw_jira_ticket_url_is_invalid(
        string $scenario,
        string $invalidJiraTicketUrl,
    ): void {
        $this->expectException(ValidationFailedException::class);

        JiraTicketUrl::fromString($invalidJiraTicketUrl);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidJiraTicketUrl: string,
     * }>
     */
    public static function invalidJiraTicketUrlProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty (``)',
            'invalidJiraTicketUrl' => '',
        ];
        $url = 'https://company.atlassian.net/PRJ-4423';
        yield [
            'scenario' => "has no browse path (`{$url}`)",
            'invalidJiraTicketUrl' => $url,
        ];
        $url = 'https://company.atlassian.net/browse/prj-4423';
        yield [
            'scenario' => "has lowercase ticket id (`{$url}`)",
            'invalidJiraTicketUrl' => $url,
        ];
    }
}
