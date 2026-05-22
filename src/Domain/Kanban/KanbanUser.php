<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban;

use Ssc\Dtk\Domain\Kanban\KanbanUser\KanbanUserId;

/**
 * @object-type Entity
 */
final readonly class KanbanUser
{
    public function __construct(
        public KanbanUserId $kanbanUserId,
    ) {
    }
}
