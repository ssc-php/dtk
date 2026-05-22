<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Exception\ConflictException;
use Ssc\Dtk\Domain\Exception\NotFoundException;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Kanban\MoveKanbanTicket;
use Ssc\Dtk\Domain\Kanban\TicketUrl;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Request:
 *
 * ```
 * POST /rest/api/3/issue/{ticketId}/transitions HTTP/1.1
 * Authorization: Basic {encodedToken}
 * Content-Type: application/json
 *
 * {
 *   "transition": {
 *     "id": {transitionId}
 *   }
 * }
 * ```
 *
 * Successful Response:
 *
 * ```
 * HTTP/1.1 204 No Content
 * ```
 *
 * Failure Responses:
 *
 * - `400 BAD REQUEST`: no transition specified, or user does not have permission to transition the issue
 * - `401 UNAUTHORIZED`: auth credentials are incorrect or missing
 * - `404 NOT FOUND`: issue does not exist, or user does not have permission to view it
 * - `409 CONFLICT`: issue could not be updated due to a conflicting update
 * - `413 REQUEST ENTITY TOO LARGE`: a per-issue limit was breached for comments, worklogs, attachments, or links
 * - `422 UNPROCESSABLE ENTITY`: a configuration problem prevents the transition
 *
 * @see https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issues/#api-rest-api-3-issue-issueidorkey-transitions-post
 */
#[AsAlias(MoveKanbanTicket::class)]
final readonly class MoveJiraTicket implements MoveKanbanTicket
{
    public function __construct(
        private GetJiraTicketTransitions $getJiraColumns,
        private BuildJiraHttpClient $buildJiraHttpClient,
    ) {
    }

    /**
     * @throws ValidationFailedException If the ticket URL is invalid
     * @throws ValidationFailedException If the column does not exist on the Jira board
     * @throws ValidationFailedException If the User does not have permission to transition the Ticket (400)
     * @throws UnauthorizedException     If the Token is rejected by the Jira API (401)
     * @throws NotFoundException         If the Ticket is not found, or User isn't allowed to view it (404)
     * @throws ConflictException         If the Ticket Transition ends up in Conflict (409)
     * @throws ServerErrorException      If the Jira API returns an unexpected error
     */
    public function move(TicketUrl $ticketUrl, string $columnName): void
    {
        $jiraTicketUrl = JiraTicketUrl::fromString($ticketUrl->toString());
        $transitions = $this->getJiraColumns->get($jiraTicketUrl);
        $ticketId = $jiraTicketUrl->toTicketId()->toString();
        $transitionId = null;

        foreach ($transitions as $transition) {
            if ($columnName === $transition->jiraColumn->name->toString()) {
                $transitionId = $transition->jiraTransitionId->toString();
                break;
            }
        }

        if (null === $transitionId) {
            throw ValidationFailedException::make(
                "No '{$columnName}' transition found for ticket {$ticketId}: does your Jira board have a column called '{$columnName}'?",
            );
        }

        $client = $this->buildJiraHttpClient->build($jiraTicketUrl->toBaseUrl());
        $response = $client->request('POST', "/rest/api/3/issue/{$ticketId}/transitions", [
            'json' => [
                'transition' => ['id' => $transitionId],
            ],
        ]);
        $statusCode = $response->getStatusCode();

        match (true) {
            400 === $statusCode => throw ValidationFailedException::make(
                "Bad request: User does not have permission to transition Ticket `{$ticketId}` to `{$transitionId}`",
            ),
            401 === $statusCode => throw UnauthorizedException::make(
                'Unauthorized: Token rejected by the Jira API',
            ),
            404 === $statusCode => throw NotFoundException::make(
                "Not found: Ticket `{$ticketId}` not found, or user isn't allowed to view it",
            ),
            409 === $statusCode => throw ConflictException::make(
                "Conflict: Ticket `{$ticketId}` Transition ends up in Conflict",
            ),
            204 !== $statusCode => throw ServerErrorException::make(
                "Unexpected Jira API response: `{$statusCode}` returned",
            ),
            default => null,
        };
    }
}
