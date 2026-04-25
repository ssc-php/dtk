<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Infrastructure\Git;

final readonly class GitWriteUncommittedChange
{
    public static function run(string $dir): void
    {
        file_put_contents("{$dir}/turnip.txt", 'A bigger turnip.');
    }
}
