<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Kanban\Jira\JiraTransition;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTransition\JiraTransitionIdFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira\JiraTransition\JiraTransitionNameFixture;

final readonly class JiraTransitionFixture
{
    public static function make(): JiraTransition
    {
        return new JiraTransition(
            JiraTransitionIdFixture::make(),
            JiraTransitionNameFixture::make(),
            JiraColumnFixture::make(),
        );
    }
}
