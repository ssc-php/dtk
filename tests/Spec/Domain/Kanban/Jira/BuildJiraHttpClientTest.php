<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Ssc\Dtk\Domain\Kanban\BaseUrl;
use Ssc\Dtk\Domain\Kanban\Jira\BuildJiraHttpClient;
use Ssc\Dtk\Domain\Token\ReadToken;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTokenFixture;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(BuildJiraHttpClient::class)]
#[Small]
final class BuildJiraHttpClientTest extends TestCase
{
    use ProphecyTrait;

    #[DataProvider('validBaseUrlProvider')]
    #[TestDox('It builds a JIRA HTTP client: $scenario')]
    public function test_it_builds_a_jira_http_client(
        string $scenario,
        string $baseUrl,
    ): void {
        $token = JiraTokenFixture::make();
        $encodedToken = base64_encode($token->toString());

        $jiraClient = $this->prophesize(HttpClientInterface::class);

        $readToken = $this->prophesize(ReadToken::class);
        $readToken->read(Service::Jira)->willReturn($token);

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->withOptions([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => "Basic {$encodedToken}",
                'Content-Type' => 'application/json',
            ],
        ])->willReturn(
            $jiraClient->reveal(),
        );

        $buildJiraHttpClient = new BuildJiraHttpClient(
            $httpClient->reveal(),
            $readToken->reveal(),
        );

        $this->assertSame(
            $jiraClient->reveal(),
            $buildJiraHttpClient->build(BaseUrl::fromString($baseUrl)),
        );
    }

    /**
     * @return \Iterator<array{scenario: string, baseUrl: string}>
     */
    public static function validBaseUrlProvider(): \Iterator
    {
        yield [
            'scenario' => 'cloud URL',
            'baseUrl' => 'https://company.atlassian.net',
        ];

        yield [
            'scenario' => 'self-hosted with http',
            'baseUrl' => 'http://jira.company.com',
        ];

        yield [
            'scenario' => 'self-hosted with port',
            'baseUrl' => 'https://jira.company.com:8080',
        ];
    }
}
