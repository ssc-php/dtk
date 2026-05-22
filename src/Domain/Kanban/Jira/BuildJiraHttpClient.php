<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Kanban\BaseUrl;
use Ssc\Dtk\Domain\Token\ReadToken;
use Ssc\Dtk\Domain\Token\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class BuildJiraHttpClient
{
    public function __construct(
        private HttpClientInterface $client,
        private ReadToken $readToken,
    ) {
    }

    public function build(BaseUrl $baseUrl): HttpClientInterface
    {
        $token = $this->readToken->read(Service::Jira);
        $encodedToken = base64_encode($token->toString());

        return $this->client->withOptions([
            'base_uri' => $baseUrl->toString(),
            'headers' => [
                'Authorization' => "Basic {$encodedToken}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
