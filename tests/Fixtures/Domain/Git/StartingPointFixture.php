<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Git;

use Ssc\Dtk\Domain\Git\StartingPoint;

final readonly class StartingPointFixture
{
    public static function makeString(): string
    {
        return 'origin/main';
    }

    public static function make(): StartingPoint
    {
        return StartingPoint::fromString(self::makeString());
    }
}
