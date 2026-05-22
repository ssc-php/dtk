<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Kanban\Jira\JiraColumn;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumn\JiraColumnIdFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumn\JiraColumnNameFixture;

#[CoversClass(JiraColumn::class)]
#[Small]
final class JiraColumnTest extends TestCase
{
    #[TestDox('It has JiraColumnId')]
    public function test_it_has_jira_column_id(): void
    {
        $jiraColumnId = JiraColumnIdFixture::make();
        $jiraColumn = new JiraColumn($jiraColumnId, JiraColumnNameFixture::make());

        $this->assertSame($jiraColumnId, $jiraColumn->id);
    }

    #[TestDox('It has JiraColumnName')]
    public function test_it_has_jira_column_name(): void
    {
        $jiraColumnName = JiraColumnNameFixture::make();
        $jiraColumn = new JiraColumn(JiraColumnIdFixture::make(), $jiraColumnName);

        $this->assertSame($jiraColumnName, $jiraColumn->name);
    }
}
