<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumn;

use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnName;

final readonly class JiraColumnNameFixture
{
    public static function make(): JiraColumnName
    {
        return JiraColumnName::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'In Progress';
    }
}
