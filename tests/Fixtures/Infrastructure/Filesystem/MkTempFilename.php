<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem;

final readonly class MkTempFilename
{
    public static function run(): string
    {
        return sys_get_temp_dir().'/dtk-'.bin2hex(random_bytes(6));
    }
}
