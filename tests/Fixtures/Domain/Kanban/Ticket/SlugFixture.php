<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Ticket;

use Ssc\Dtk\Domain\Kanban\Ticket\Slug;

final readonly class SlugFixture
{
    public static function make(): Slug
    {
        return Slug::fromString(TitleFixture::makeString());
    }

    public static function makeString(): string
    {
        return 'fix-broken-login';
    }
}
