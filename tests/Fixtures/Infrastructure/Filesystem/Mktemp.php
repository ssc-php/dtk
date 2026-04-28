<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem;

use Symfony\Component\Process\Process;

final readonly class Mktemp
{
    public static function run(): string
    {
        $process = new Process(['mktemp', '-d']);
        $process->mustRun();

        return trim($process->getOutput());
    }
}
