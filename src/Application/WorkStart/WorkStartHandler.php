<?php

declare(strict_types=1);

namespace Ssc\Dtk\Application\WorkStart;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Git\BranchName;
use Ssc\Dtk\Domain\Git\Command\SwitchToNewBranch;
use Ssc\Dtk\Domain\Git\StartingPoint;
use Ssc\Dtk\Domain\Kanban\GetKanbanTicket;
use Ssc\Dtk\Domain\Kanban\MoveKanbanTicket;
use Ssc\Dtk\Domain\Kanban\TicketUrl;
use Ssc\Dtk\Domain\Template\Replace;

/**
 * @object-type UseCase
 */
final readonly class WorkStartHandler
{
    public function __construct(
        private GetKanbanTicket $getKanbanTicket,
        private Replace $replace,
        private SwitchToNewBranch $switchToNewBranch,
        private MoveKanbanTicket $moveKanbanTicket,
    ) {
    }

    /**
     * @throws ValidationFailedException If the ticket URL is invalid, the ticket is not found, or the column does not exist
     * @throws UnauthorizedException     If the Token is rejected by the Kanban Board API
     * @throws ServerErrorException      If a git or board API operation fails unexpectedly
     */
    public function handle(WorkStart $workStart): void
    {
        $ticketUrl = null;
        $branchNameParameters = [
            'ticket_id' => $workStart->ticketId,
        ];

        if ('' !== $workStart->ticketUrl) {
            $ticketUrl = TicketUrl::fromString($workStart->ticketUrl);
            $ticket = $this->getKanbanTicket->get($ticketUrl);
            $branchNameParameters = [
                'ticket_id' => $ticket->ticketId->toString(),
                'title' => $ticket->title->toSlug()->toString(),
                'type' => $ticket->type->toSlug()->toString(),
            ];
        }

        $this->switchToNewBranch->switch(
            BranchName::fromString($this->replace->in(
                $workStart->newBranch,
                $branchNameParameters,
            )),
            StartingPoint::fromString($workStart->startingPoint),
            $workStart->autostash,
        );

        if ($ticketUrl instanceof TicketUrl) {
            $this->moveKanbanTicket->move($ticketUrl, 'In Progress');
        }
    }
}
