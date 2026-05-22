<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Jira;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraColumnFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTransition\JiraTransitionIdFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTransition\JiraTransitionNameFixture;

#[CoversClass(JiraTransition::class)]
#[Small]
final class JiraTransitionTest extends TestCase
{
    #[TestDox('It has JiraTransitionId')]
    public function test_it_has_jira_transition_id(): void
    {
        $jiraTransitionId = JiraTransitionIdFixture::make();
        $jiraTransition = new JiraTransition(
            $jiraTransitionId,
            JiraTransitionNameFixture::make(),
            JiraColumnFixture::make(),
        );

        $this->assertSame($jiraTransitionId, $jiraTransition->jiraTransitionId);
    }

    #[TestDox('It has JiraTransitionName')]
    public function test_it_has_jira_transition_name(): void
    {
        $jiraTransitionName = JiraTransitionNameFixture::make();
        $jiraTransition = new JiraTransition(
            JiraTransitionIdFixture::make(),
            $jiraTransitionName,
            JiraColumnFixture::make(),
        );

        $this->assertSame($jiraTransitionName, $jiraTransition->jiraTransitionName);
    }

    #[TestDox('It has JiraColumn')]
    public function test_it_has_jira_column(): void
    {
        $jiraColumn = JiraColumnFixture::make();
        $jiraTransition = new JiraTransition(
            JiraTransitionIdFixture::make(),
            JiraTransitionNameFixture::make(),
            $jiraColumn,
        );

        $this->assertSame($jiraColumn, $jiraTransition->jiraColumn);
    }
}
