<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Exception\NotFoundException;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\GetKanbanTicket;
use Ssc\Dtk\Domain\Kanban\Ticket;
use Ssc\Dtk\Domain\Kanban\Ticket\TicketId;
use Ssc\Dtk\Domain\Kanban\Ticket\Title;
use Ssc\Dtk\Domain\Kanban\Ticket\Type;
use Ssc\Dtk\Domain\Kanban\TicketUrl;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Request:
 *
 * ```
 * GET /rest/api/3/issue/{ticketId}?fields=summary,issuetype HTTP/1.1
 * Authorization: Basic {encodedToken}
 * Content-Type: application/json
 * ```
 *
 * Successful Response:
 *
 * ```
 * HTTP/1.1 200 OK
 * Content-Type: application/json
 *
 * {
 *   "key": {ticketId},
 *   "fields": {
 *     "summary": {title},
 *     "issuetype": {
 *       "name": {type}
 *     }
 *   }
 * }
 * ```
 *
 * Failure Responses:
 *
 * - `401 UNAUTHORIZED`: auth credentials are incorrect or invalid
 * - `404 NOT FOUND`: ticket does not exist, or user doesn't have permission to view it
 *
 * @see https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issues/#api-rest-api-3-issue-issueidorkey-get
 */
#[AsAlias(GetKanbanTicket::class)]
final readonly class GetJiraTicket implements GetKanbanTicket
{
    public function __construct(
        private BuildJiraHttpClient $buildJiraHttpClient,
    ) {
    }

    /**
     * @throws ValidationFailedException If the ticket URL is invalid
     * @throws UnauthorizedException     If the Token is rejected by the Jira API (401)
     * @throws NotFoundException         If the Ticket is not found, or User isn't allowed to view it (404)
     * @throws ServerErrorException      If the Jira API returns an unexpected error
     */
    public function get(TicketUrl $ticketUrl): Ticket
    {
        $jiraTicketUrl = JiraTicketUrl::fromString($ticketUrl->toString());
        $client = $this->buildJiraHttpClient->build($jiraTicketUrl->toBaseUrl());
        $ticketId = $jiraTicketUrl->toTicketId()->toString();

        $response = $client->request('GET', "/rest/api/3/issue/{$ticketId}?fields=summary,issuetype");
        $statusCode = $response->getStatusCode();

        match (true) {
            401 === $statusCode => throw UnauthorizedException::make(
                'Unauthorized: Token rejected by the Jira API',
            ),
            404 === $statusCode => throw NotFoundException::make(
                "Not found: Ticket `{$ticketId}` not found, or user isn't allowed to view it",
            ),
            200 !== $statusCode => throw ServerErrorException::make(
                "Unexpected Jira API response: `{$statusCode}` returned",
            ),
            default => null,
        };

        /**
         * @var array{
         *      key: string,
         *      fields: array{
         *          summary?: string,
         *          issuetype?: array{name?: string},
         *      },
         *  } $data
         */
        $data = $response->toArray();

        return new Ticket(
            TicketId::fromString($data['key']),
            Title::fromString($data['fields']['summary'] ?? ''),
            Type::fromString($data['fields']['issuetype']['name'] ?? ''),
        );
    }
}
