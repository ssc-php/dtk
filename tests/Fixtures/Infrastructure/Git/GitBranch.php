<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Infrastructure\Git;

use Symfony\Component\Process\Process;

final readonly class GitBranch
{
    public static function run(string $dir, string $name): void
    {
        new Process(['git', 'branch', $name], $dir)->mustRun();
    }
}
