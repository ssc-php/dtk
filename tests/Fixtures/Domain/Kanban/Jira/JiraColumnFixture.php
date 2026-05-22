<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumn\JiraColumnIdFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumn\JiraColumnNameFixture;

final readonly class JiraColumnFixture
{
    public static function make(): JiraColumn
    {
        return new JiraColumn(
            JiraColumnIdFixture::make(),
            JiraColumnNameFixture::make(),
        );
    }
}
