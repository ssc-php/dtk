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
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Kanban\Jira\BuildJiraHttpClient;
use Ssc\Dtk\Domain\Kanban\Jira\GetCurrentJiraUser;
use Ssc\Dtk\Domain\Token\ReadToken;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTokenFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\TicketUrlFixture;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(GetCurrentJiraUser::class)]
#[Small]
final class GetCurrentJiraUserTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox('It gets the current Kanban user')]
    public function test_it_gets_the_current_kanban_user(): void
    {
        $accountId = 'abc123';

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->toArray()->willReturn([
            'accountId' => $accountId,
        ]);

        $jiraClient = $this->prophesize(HttpClientInterface::class);
        $jiraClient->request('GET', '/rest/api/3/myself')->willReturn($response->reveal());

        $getCurrentJiraUser = new GetCurrentJiraUser(
            $this->buildJiraHttpClient($jiraClient->reveal()),
        );

        $user = $getCurrentJiraUser->get(TicketUrlFixture::make());

        $this->assertSame($accountId, $user->kanbanUserId->toString());
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
        $jiraClient->request(
            'GET',
            '/rest/api/3/myself',
        )->willReturn(
            $response->reveal(),
        );

        $getCurrentJiraUser = new GetCurrentJiraUser(
            $this->buildJiraHttpClient($jiraClient->reveal()),
        );

        $this->expectException($exceptionClass);
        $getCurrentJiraUser->get(TicketUrlFixture::make());
    }

    /**
     * @return \Iterator<array{
     *      scenario: string,
     *      httpCode: int,
     *      exceptionClass: class-string<\Throwable>,
     * }>
     */
    public static function failureProvider(): \Iterator
    {
        yield [
            'scenario' => 'Token rejected by the Jira API (401 Unauthorized)',
            'httpCode' => 401,
            'exceptionClass' => UnauthorizedException::class,
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
