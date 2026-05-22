<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\UnauthorizedException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;

interface MoveKanbanTicket
{
    /**
     * @throws ValidationFailedException If the ticket URL is invalid or the column does not exist
     * @throws UnauthorizedException     If the auth token is incorrect
     * @throws ServerErrorException      If the board API returns an unexpected error
     */
    public function move(TicketUrl $ticketUrl, string $columnName): void;
}
