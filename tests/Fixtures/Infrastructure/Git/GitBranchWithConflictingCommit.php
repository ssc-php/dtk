<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Infrastructure\Git;

use Symfony\Component\Process\Process;

final readonly class GitBranchWithConflictingCommit
{
    public static function run(string $dir, string $name): void
    {
        new Process(['git', 'switch', '-c', $name], $dir)->mustRun();
        file_put_contents("{$dir}/turnip.txt", 'A completely different turnip.');
        new Process(['git', 'add', '.'], $dir)->mustRun();
        new Process(['git', 'commit', '-m', 'A competing turnip agenda'], $dir)->mustRun();
        new Process(['git', 'switch', 'main'], $dir)->mustRun();
        file_put_contents("{$dir}/turnip.txt", 'A bigger turnip.');
    }
}
