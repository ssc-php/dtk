<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumn;

use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn\JiraColumnId;

final readonly class JiraColumnIdFixture
{
    public static function make(): JiraColumnId
    {
        return JiraColumnId::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return '2';
    }
}
