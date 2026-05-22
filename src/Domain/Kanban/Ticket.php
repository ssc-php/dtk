<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban;

use Ssc\Dtk\Domain\Kanban\Ticket\TicketId;
use Ssc\Dtk\Domain\Kanban\Ticket\Title;
use Ssc\Dtk\Domain\Kanban\Ticket\Type;

/**
 * @object-type Entity
 */
final readonly class Ticket
{
    public function __construct(
        public TicketId $ticketId,
        public Title $title,
        public Type $type,
    ) {
    }
}
