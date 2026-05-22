<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban;

use Ssc\Dtk\Domain\Kanban\BaseUrl;

final readonly class BaseUrlFixture
{
    public static function make(): BaseUrl
    {
        return BaseUrl::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'https://company.atlassian.net';
    }
}
