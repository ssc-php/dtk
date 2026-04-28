<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Application\WorkStart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Ssc\Dtk\Application\WorkStart\WorkStart;
use Ssc\Dtk\Application\WorkStart\WorkStartHandler;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\BranchName;
use Ssc\Dtk\Domain\Git\Command\SwitchToNewBranch;
use Ssc\Dtk\Domain\Git\StartingPoint;
use Ssc\Dtk\Domain\Template\Replace;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\BranchNameFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\StartingPointFixture;

#[CoversClass(WorkStartHandler::class)]
#[Small]
final class WorkStartHandlerTest extends TestCase
{
    use ProphecyTrait;

    #[DataProvider('createBranchProvider')]
    #[TestDox('It creates the branch when: $scenario')]
    public function test_it_creates_the_branch_when(
        string $scenario,
        bool $autostash,
    ): void {
        // Fixtures
        $newBranch = BranchNameFixture::makeString();
        $startingPoint = StartingPointFixture::makeString();

        // Test doubles
        $switchToNewBranch = $this->prophesize(SwitchToNewBranch::class);
        $switchToNewBranch->switch(
            Argument::that(static fn (BranchName $b): bool => $b->toString() === $newBranch),
            Argument::that(static fn (StartingPoint $c): bool => $c->toString() === $startingPoint),
            $autostash,
        )->shouldBeCalledOnce();

        // System under test
        $workStartHandler = new WorkStartHandler(new Replace(), $switchToNewBranch->reveal());
        $workStartHandler->handle(new WorkStart(
            $newBranch,
            $startingPoint,
            autostash: $autostash,
        ));
    }

    /**
     * @return \Iterator<array{scenario: string, autostash: bool}>
     */
    public static function createBranchProvider(): \Iterator
    {
        yield [
            'scenario' => 'there are no uncommitted changes',
            'autostash' => false,
        ];
        yield [
            'scenario' => 'there are uncommitted changes, and autostash is on',
            'autostash' => true,
        ];
    }

    #[DataProvider('placeholderProvider')]
    #[TestDox('It creates the branch when: newBranch contains $scenario placeholder')]
    public function test_it_creates_the_branch_when_the_branch_name_contains_placeholders(
        string $scenario,
        string $branchNameTemplate,
        string $ticketId,
        string $expectedBranch,
    ): void {
        // Fixtures
        $startingPoint = StartingPointFixture::makeString();
        $autostashOff = false;

        // Test doubles
        $switchToNewBranch = $this->prophesize(SwitchToNewBranch::class);
        $switchToNewBranch->switch(
            Argument::that(static fn (BranchName $b): bool => $b->toString() === $expectedBranch),
            Argument::that(static fn (StartingPoint $c): bool => $c->toString() === $startingPoint),
            $autostashOff,
        )->shouldBeCalledOnce();

        // System under test
        $workStartHandler = new WorkStartHandler(new Replace(), $switchToNewBranch->reveal());
        $workStartHandler->handle(new WorkStart(
            $branchNameTemplate,
            $startingPoint,
            $ticketId,
            $autostashOff,
        ));
    }

    /**
     * @return \Iterator<array{scenario: string, branchNameTemplate: string, ticketId: string, expectedBranch: string}>
     */
    public static function placeholderProvider(): \Iterator
    {
        yield [
            'scenario' => '{ticket_id}',
            'branchNameTemplate' => '{ticket_id}/feat/cunning-plan',
            'ticketId' => 'PRJ-4423',
            'expectedBranch' => 'PRJ-4423/feat/cunning-plan',
        ];
    }

    #[TestDox('It fails when: there are uncommitted changes, and autostash is off')]
    public function test_it_fails_when_uncommitted_changes_and_autostash_is_off(): void
    {
        // Fixtures
        $newBranch = BranchNameFixture::makeString();
        $startingPoint = StartingPointFixture::makeString();
        $autostashOff = false;

        // Test doubles
        $switchToNewBranch = $this->prophesize(SwitchToNewBranch::class);
        $switchToNewBranch->switch(
            Argument::that(static fn (BranchName $b): bool => $b->toString() === $newBranch),
            Argument::that(static fn (StartingPoint $c): bool => $c->toString() === $startingPoint),
            $autostashOff,
        )->willThrow(ServerErrorException::class);

        // System under test
        $workStartHandler = new WorkStartHandler(new Replace(), $switchToNewBranch->reveal());

        $this->expectException(ServerErrorException::class);
        $workStartHandler->handle(new WorkStart(
            $newBranch,
            $startingPoint,
            autostash: $autostashOff,
        ));
    }
}
