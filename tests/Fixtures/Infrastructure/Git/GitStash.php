<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Infrastructure\Git;

use Symfony\Component\Process\Process;

final readonly class GitStash
{
    public static function run(string $dir): void
    {
        new Process(['git', 'stash'], $dir)->mustRun();
    }
}
