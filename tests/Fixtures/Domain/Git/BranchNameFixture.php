<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Git;

use Ssc\Dtk\Domain\Git\BranchName;

final readonly class BranchNameFixture
{
    public static function makeString(): string
    {
        return 'feat/cunning-plan-'.bin2hex(random_bytes(4));
    }

    public static function make(): BranchName
    {
        return BranchName::fromString(self::makeString());
    }
}
