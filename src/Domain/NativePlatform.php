<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain;

use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(Platform::class)]
final class NativePlatform implements Platform
{
    public function getOsFamily(): string
    {
        return \PHP_OS_FAMILY;
    }
}
