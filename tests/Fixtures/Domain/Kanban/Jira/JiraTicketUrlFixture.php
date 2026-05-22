<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Kanban\Jira\JiraTicketUrl;

final readonly class JiraTicketUrlFixture
{
    public static function make(): JiraTicketUrl
    {
        return JiraTicketUrl::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'https://company.atlassian.net/browse/PRJ-4423';
    }
}
