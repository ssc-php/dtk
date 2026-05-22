<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Exception\NotFoundException;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnId;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnName;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionId;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionName;

/**
 * In Jira, a column (status) is the state a ticket currently sits in (e.g. "In Progress").
 * A transition is the action that moves a ticket to a new status (e.g. "Start Progress").
 * Transitions have their own IDs and names; each carries its destination status in a `to` field.
 *
 * Request:
 *
 * ```
 * GET /rest/api/3/issue/{ticketId}/transitions HTTP/1.1
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
 *   "transitions": [
 *     {
 *       "id": {transitionId},
 *       "name": {transitionName},
 *       "to": {
 *         "id": {statusId},
 *         "name": {statusName}
 *       }
 *     }
 *   ]
 * }
 * ```
 *
 * Failure Responses:
 *
 * - `401 UNAUTHORIZED`: auth credentials are incorrect or missing
 * - `404 NOT FOUND`: issue does not exist, or user does not have permission to view it
 *
 * @see https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issues/#api-rest-api-3-issue-issueidorkey-transitions-get
 */
final readonly class GetJiraTicketTransitions
{
    public function __construct(
        private BuildJiraHttpClient $buildJiraHttpClient,
    ) {
    }

    /**
     * @return list<JiraTransition>
     *
     * @throws UnauthorizedException If the Token is rejected by the Jira API (401)
     * @throws NotFoundException     If the Ticket is not found, or User isn't allowed to view it (404)
     * @throws ServerErrorException  If the Jira API returns an unexpected error
     */
    public function get(JiraTicketUrl $jiraTicketUrl): array
    {
        $client = $this->buildJiraHttpClient->build($jiraTicketUrl->toBaseUrl());
        $ticketId = $jiraTicketUrl->toTicketId()->toString();

        $response = $client->request('GET', "/rest/api/3/issue/{$ticketId}/transitions");
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
         *     transitions: list<array{
         *         id: string,
         *         name: string,
         *         to: array{
         *             id: string,
         *             name: string,
         *         },
         *     }>,
         * } $data
         */
        $data = $response->toArray();

        $transitions = [];
        foreach ($data['transitions'] as $transition) {
            $transitions[] = new JiraTransition(
                JiraTransitionId::fromString($transition['id']),
                JiraTransitionName::fromString($transition['name']),
                new JiraColumn(
                    JiraColumnId::fromString($transition['to']['id']),
                    JiraColumnName::fromString($transition['to']['name']),
                ),
            );
        }

        return $transitions;
    }
}
