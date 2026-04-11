<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain;

interface Platform
{
    public function getOsFamily(): string;
}
