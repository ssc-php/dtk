<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Ssc\Dtk\Domain\Exception\NotFoundException;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Kanban\Jira\BuildJiraHttpClient;
use Ssc\Dtk\Domain\Kanban\Jira\GetJiraTicketTransitions;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnId;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnName;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionId;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionName;
use Ssc\Dtk\Domain\Token\ReadToken;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTicketUrlFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTokenFixture;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(GetJiraTicketTransitions::class)]
#[Small]
final class GetJiraTicketTransitionsTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox('It gets the Jira ticket transitions')]
    public function test_it_gets_the_jira_ticket_transitions(): void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->toArray()->willReturn(['transitions' => [
            ['id' => '11', 'name' => 'Go to To Do',       'to' => ['id' => '1', 'name' => 'To Do']],
            ['id' => '21', 'name' => 'Start Progress',    'to' => ['id' => '2', 'name' => 'In Progress']],
            ['id' => '31', 'name' => 'Mark as Done',      'to' => ['id' => '3', 'name' => 'Done']],
        ]]);

        $jiraClient = $this->prophesize(HttpClientInterface::class);
        $jiraClient->request('GET', '/rest/api/3/issue/PRJ-4423/transitions')->willReturn($response->reveal());

        $getJiraTicketTransitions = new GetJiraTicketTransitions(
            $this->buildJiraHttpClient($jiraClient->reveal()),
        );

        $transitions = $getJiraTicketTransitions->get(JiraTicketUrlFixture::make());

        $this->assertEquals([
            new JiraTransition(
                JiraTransitionId::fromString('11'),
                JiraTransitionName::fromString('Go to To Do'),
                new JiraColumn(JiraColumnId::fromString('1'), JiraColumnName::fromString('To Do')),
            ),
            new JiraTransition(
                JiraTransitionId::fromString('21'),
                JiraTransitionName::fromString('Start Progress'),
                new JiraColumn(JiraColumnId::fromString('2'), JiraColumnName::fromString('In Progress')),
            ),
            new JiraTransition(
                JiraTransitionId::fromString('31'),
                JiraTransitionName::fromString('Mark as Done'),
                new JiraColumn(JiraColumnId::fromString('3'), JiraColumnName::fromString('Done')),
            ),
        ], $transitions);
    }

    /**
     * @param class-string<\Throwable> $exceptionClass
     */
    #[DataProvider('failureProvider')]
    #[TestDox('It fails when: $scenario')]
    public function test_it_fails(
        string $scenario,
        int $httpCode,
        string $exceptionClass,
    ): void {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn($httpCode);

        $jiraClient = $this->prophesize(HttpClientInterface::class);
        $jiraClient->request('GET', '/rest/api/3/issue/PRJ-4423/transitions')->willReturn($response->reveal());

        $getJiraTicketTransitions = new GetJiraTicketTransitions(
            $this->buildJiraHttpClient($jiraClient->reveal()),
        );

        $this->expectException($exceptionClass);
        $getJiraTicketTransitions->get(JiraTicketUrlFixture::make());
    }

    /**
     * @return \Iterator<array{scenario: string, httpCode: int, exceptionClass: class-string<\Throwable>}>
     */
    public static function failureProvider(): \Iterator
    {
        yield [
            'scenario' => 'Token rejected by the Jira API (401 Unauthorized)',
            'httpCode' => 401,
            'exceptionClass' => UnauthorizedException::class,
        ];

        yield [
            'scenario' => "Ticket not found, or User isn't allowed to view it (404 Not Found)",
            'httpCode' => 404,
            'exceptionClass' => NotFoundException::class,
        ];

        yield [
            'scenario' => 'Jira API Server Error (500 Internal Server Error)',
            'httpCode' => 500,
            'exceptionClass' => ServerErrorException::class,
        ];
    }

    private function buildJiraHttpClient(HttpClientInterface $jiraClient): BuildJiraHttpClient
    {
        $readToken = $this->prophesize(ReadToken::class);
        $readToken->read(Service::Jira)->willReturn(JiraTokenFixture::make());

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->withOptions(Argument::any())->willReturn($jiraClient);

        return new BuildJiraHttpClient($httpClient->reveal(), $readToken->reveal());
    }
}
