<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTransition;

use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition\JiraTransitionName;

final readonly class JiraTransitionNameFixture
{
    public static function make(): JiraTransitionName
    {
        return JiraTransitionName::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'Start Progress';
    }
}
