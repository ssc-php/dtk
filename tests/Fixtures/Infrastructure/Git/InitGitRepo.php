<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Infrastructure\Git;

use Symfony\Component\Process\Process;

final readonly class InitGitRepo
{
    public static function run(string $dir): void
    {
        new Process(['git', 'init', '-b', 'main'], $dir)->mustRun();
        new Process(['git', 'config', 'user.email', 'e.blackadder@example.com'], $dir)->mustRun();
        new Process(['git', 'config', 'user.name', 'Edmund Blackadder'], $dir)->mustRun();
        file_put_contents("{$dir}/turnip.txt", 'A turnip somewhat resembling a winkle.');
        new Process(['git', 'add', '.'], $dir)->mustRun();
        new Process(['git', 'commit', '-m', 'Definitely NOT treason'], $dir)->mustRun();
        new Process(['git', 'remote', 'add', 'origin', $dir], $dir)->mustRun();
        new Process(['git', 'fetch', 'origin'], $dir)->mustRun();
        chdir($dir);
    }
}
