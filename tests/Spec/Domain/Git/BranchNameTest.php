<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Git;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Git\BranchName;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\BranchNameFixture;

#[CoversClass(BranchName::class)]
#[Small]
final class BranchNameTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringBranchName = BranchNameFixture::makeString();
        $branchName = BranchName::fromString($stringBranchName);

        $this->assertInstanceOf(BranchName::class, $branchName);
        $this->assertSame($stringBranchName, $branchName->toString());
    }
}
