<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTransition;

use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionId;

final readonly class JiraTransitionIdFixture
{
    public static function make(): JiraTransitionId
    {
        return JiraTransitionId::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return '21';
    }
}
