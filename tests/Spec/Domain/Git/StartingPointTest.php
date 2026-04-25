<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Git;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Git\StartingPoint;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\StartingPointFixture;

#[CoversClass(StartingPoint::class)]
#[Small]
final class StartingPointTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringStartingPoint = StartingPointFixture::makeString();
        $startingPoint = StartingPoint::fromString($stringStartingPoint);

        $this->assertInstanceOf(StartingPoint::class, $startingPoint);
        $this->assertSame($stringStartingPoint, $startingPoint->toString());
    }
}
