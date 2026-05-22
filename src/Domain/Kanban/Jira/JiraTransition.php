<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionId;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionName;

/**
 * @object-type Entity
 */
final readonly class JiraTransition
{
    public function __construct(
        public JiraTransitionId $jiraTransitionId,
        public JiraTransitionName $jiraTransitionName,
        public JiraColumn $jiraColumn,
    ) {
    }
}
