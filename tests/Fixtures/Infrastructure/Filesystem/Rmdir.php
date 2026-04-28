<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem;

use Symfony\Component\Process\Process;

final readonly class Rmdir
{
    public static function run(string $dir): void
    {
        new Process(['rm', '-rf', $dir])->run();
    }
}
