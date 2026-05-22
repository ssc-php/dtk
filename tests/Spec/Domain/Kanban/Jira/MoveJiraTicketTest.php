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
use Ssc\Dtk\Domain\Exception\ConflictException;
use Ssc\Dtk\Domain\Exception\NotFoundException;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\Jira\BuildJiraHttpClient;
use Ssc\Dtk\Domain\Kanban\Jira\GetJiraTicketTransitions;
use Ssc\Dtk\Domain\Kanban\Jira\MoveJiraTicket;
use Ssc\Dtk\Domain\Token\ReadToken;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTokenFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\TicketUrlFixture;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(MoveJiraTicket::class)]
#[Small]
final class MoveJiraTicketTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox('It moves the Kanban ticket to the given column')]
    public function test_it_moves_the_kanban_ticket_to_the_given_column(): void
    {
        $transitionsResponse = $this->prophesize(ResponseInterface::class);
        $transitionsResponse->getStatusCode()->willReturn(200);
        $transitionsResponse->toArray()->willReturn(['transitions' => [
            ['id' => '11', 'name' => 'Go to To Do',    'to' => ['id' => '1', 'name' => 'To Do']],
            ['id' => '21', 'name' => 'Start Progress', 'to' => ['id' => '2', 'name' => 'In Progress']],
            ['id' => '31', 'name' => 'Mark as Done',   'to' => ['id' => '3', 'name' => 'Done']],
        ]]);

        $updateResponse = $this->prophesize(ResponseInterface::class);
        $updateResponse->getStatusCode()->willReturn(204);

        $jiraClient = $this->prophesize(HttpClientInterface::class);
        $jiraClient->request('GET', '/rest/api/3/issue/PRJ-4423/transitions')->willReturn($transitionsResponse->reveal());
        $jiraClient->request('POST', '/rest/api/3/issue/PRJ-4423/transitions', ['json' => ['transition' => ['id' => '21']]])->willReturn($updateResponse->reveal());

        $moveJiraTicket = new MoveJiraTicket(
            $this->getJiraTicketTransitions($jiraClient->reveal()),
            $this->buildJiraHttpClient($jiraClient->reveal()),
        );

        $this->expectNotToPerformAssertions();
        $moveJiraTicket->move(TicketUrlFixture::make(), 'In Progress');
    }

    #[TestDox('It fails when: Ticket Transition is not found')]
    public function test_it_fails_when_ticket_transition_is_not_found(): void
    {
        $transitionsResponse = $this->prophesize(ResponseInterface::class);
        $transitionsResponse->getStatusCode()->willReturn(200);
        $transitionsResponse->toArray()->willReturn(['transitions' => [
            ['id' => '11', 'name' => 'Go to To Do',  'to' => ['id' => '1', 'name' => 'To Do']],
            ['id' => '31', 'name' => 'Mark as Done', 'to' => ['id' => '3', 'name' => 'Done']],
        ]]);

        $jiraClient = $this->prophesize(HttpClientInterface::class);
        $jiraClient->request('GET', '/rest/api/3/issue/PRJ-4423/transitions')->willReturn($transitionsResponse->reveal());

        $moveJiraTicket = new MoveJiraTicket(
            $this->getJiraTicketTransitions($jiraClient->reveal()),
            $this->buildJiraHttpClient($jiraClient->reveal()),
        );

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/In Progress/');
        $moveJiraTicket->move(TicketUrlFixture::make(), 'In Progress');
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
        $transitionsResponse = $this->prophesize(ResponseInterface::class);
        $transitionsResponse->getStatusCode()->willReturn(200);
        $transitionsResponse->toArray()->willReturn(['transitions' => [
            ['id' => '21', 'name' => 'Start Progress', 'to' => ['id' => '2', 'name' => 'In Progress']],
        ]]);

        $updateResponse = $this->prophesize(ResponseInterface::class);
        $updateResponse->getStatusCode()->willReturn($httpCode);

        $jiraClient = $this->prophesize(HttpClientInterface::class);
        $jiraClient->request('GET', '/rest/api/3/issue/PRJ-4423/transitions')->willReturn($transitionsResponse->reveal());
        $jiraClient->request('POST', '/rest/api/3/issue/PRJ-4423/transitions', Argument::any())->willReturn($updateResponse->reveal());

        $moveJiraTicket = new MoveJiraTicket(
            $this->getJiraTicketTransitions($jiraClient->reveal()),
            $this->buildJiraHttpClient($jiraClient->reveal()),
        );

        $this->expectException($exceptionClass);
        $moveJiraTicket->move(TicketUrlFixture::make(), 'In Progress');
    }

    /**
     * @return \Iterator<array{scenario: string, httpCode: int, exceptionClass: class-string<\Throwable>}>
     */
    public static function failureProvider(): \Iterator
    {
        yield [
            'scenario' => 'User is not allowed to Transition the Ticket (400 Bad Request)',
            'httpCode' => 400,
            'exceptionClass' => ValidationFailedException::class,
        ];

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
            'scenario' => 'Ticket Transition ends up in Conflict (409 Conflict)',
            'httpCode' => 409,
            'exceptionClass' => ConflictException::class,
        ];

        yield [
            'scenario' => 'Jira API Server Error (500 Internal Server Error)',
            'httpCode' => 500,
            'exceptionClass' => ServerErrorException::class,
        ];
    }

    private function getJiraTicketTransitions(HttpClientInterface $jiraClient): GetJiraTicketTransitions
    {
        $readToken = $this->prophesize(ReadToken::class);
        $readToken->read(Service::Jira)->willReturn(JiraTokenFixture::make());

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->withOptions(Argument::any())->willReturn($jiraClient);

        return new GetJiraTicketTransitions(new BuildJiraHttpClient($httpClient->reveal(), $readToken->reveal()));
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
