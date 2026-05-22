<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnId;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnName;

/**
 * A Jira Status, which represents a column on a Jira board.
 *
 * Statuses are defined at the project level as part of a workflow.
 * Each workflow is a directed graph of Statuses (nodes) connected by Transitions (edges).
 * Board columns are then mapped to one or more Statuses from that workflow.
 *
 * @object-type Entity
 */
final readonly class JiraColumn
{
    public function __construct(
        public JiraColumnId $id,
        public JiraColumnName $name,
    ) {
    }
}
