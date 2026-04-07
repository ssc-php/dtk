<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Token;

use Ssc\Dtk\Domain\Token\Service;

final readonly class ServiceFixture
{
    public static function make(): Service
    {
        return Service::cases()[0];
    }

    public static function makeString(): string
    {
        return Service::cases()[0]->value;
    }
}
