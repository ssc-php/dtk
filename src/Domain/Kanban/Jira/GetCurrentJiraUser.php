<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\GetCurrentKanbanUser;
use Ssc\Dtk\Domain\Kanban\KanbanUser;
use Ssc\Dtk\Domain\Kanban\KanbanUser\KanbanUserId;
use Ssc\Dtk\Domain\Kanban\TicketUrl;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Request:
 *
 * ```
 * GET /rest/api/3/myself HTTP/1.1
 * Authorization: Basic {encodedToken}
 * Content-Type: application/json
 * ```
 *
 * Succesful Response:
 *
 * ```
 * HTTP/1.1 200 OK
 * Content-Type: application/json
 *
 * {
 *   "accountId": {accountId},
 *   "displayName": {displayName}
 * }
 * ```
 *
 * The following edge cases will still be retured as Succesful Responses,
 * with their values set to "" (empty), "unknown", or a fallback value:
 *
 * - deleted user (right to be forgotten)
 * - corrupted user record (failed server import)
 * - unavailable user record (internal service outage)
 *
 * Failure Responses:
 *
 * - `401 UNAUTHORIZED`: auth credentials are incorrect or invalid
 *
 * @see https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-myself/#api-rest-api-3-myself-get
 */
#[AsAlias(GetCurrentKanbanUser::class)]
final readonly class GetCurrentJiraUser implements GetCurrentKanbanUser
{
    public function __construct(
        private BuildJiraHttpClient $buildJiraHttpClient,
    ) {
    }

    /**
     * @throws ValidationFailedException If the ticket URL is invalid
     * @throws UnauthorizedException     If the Token is rejected by the Jira API (401)
     * @throws ServerErrorException      If the Jira API returns an unexpected error
     */
    public function get(TicketUrl $ticketUrl): KanbanUser
    {
        $jiraTicketUrl = JiraTicketUrl::fromString($ticketUrl->toString());
        $client = $this->buildJiraHttpClient->build($jiraTicketUrl->toBaseUrl());
        $response = $client->request('GET', '/rest/api/3/myself');

        $statusCode = $response->getStatusCode();
        match (true) {
            401 === $statusCode => throw UnauthorizedException::make(
                'Unauthorized: Token rejected by the Jira API',
            ),
            200 !== $statusCode => throw ServerErrorException::make(
                "Unexpected Jira API response: `{$statusCode}` returned",
            ),
            default => null,
        };

        /**
         * @var array{
         *      accountId: string,
         *  } $data
         */
        $data = $response->toArray();

        return new KanbanUser(
            KanbanUserId::fromString($data['accountId']),
        );
    }
}
