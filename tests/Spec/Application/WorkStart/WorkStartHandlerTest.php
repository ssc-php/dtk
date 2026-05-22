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
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Git\BranchName;
use Ssc\Dtk\Domain\Git\Command\SwitchToNewBranch;
use Ssc\Dtk\Domain\Git\StartingPoint;
use Ssc\Dtk\Domain\Kanban\GetKanbanTicket;
use Ssc\Dtk\Domain\Kanban\MoveKanbanTicket;
use Ssc\Dtk\Domain\Kanban\TicketUrl;
use Ssc\Dtk\Domain\Template\Replace;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\BranchNameFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\StartingPointFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\TicketFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Kanban\TicketUrlFixture;

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

        $getKanbanTicket = $this->prophesize(GetKanbanTicket::class);
        $getKanbanTicket->get(Argument::cetera())->shouldNotBeCalled();

        $moveKanbanTicket = $this->prophesize(MoveKanbanTicket::class);
        $moveKanbanTicket->move(Argument::cetera())->shouldNotBeCalled();

        // System under test
        $workStartHandler = new WorkStartHandler(
            $getKanbanTicket->reveal(),
            new Replace(),
            $switchToNewBranch->reveal(),
            $moveKanbanTicket->reveal(),
        );
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

    #[TestDox('It can also: use {ticket_id}, {type}, {title} placeholders in branch name')]
    public function test_it_can_also_use_placeholders_in_branch_name(): void
    {
        // Fixtures
        $startingPoint = StartingPointFixture::makeString();
        $autostashOff = false;
        $ticketUrl = TicketUrlFixture::makeString();
        $branchNameTemplate = '{type}/{ticket_id}-{title}';
        $expectedBranch = 'bug/PRJ-4423-fix-broken-login';

        // Test doubles
        $getKanbanTicket = $this->prophesize(GetKanbanTicket::class);
        $getKanbanTicket->get(
            Argument::that(static fn (TicketUrl $u): bool => $u->toString() === $ticketUrl),
        )->willReturn(TicketFixture::make());

        $switchToNewBranch = $this->prophesize(SwitchToNewBranch::class);
        $switchToNewBranch->switch(
            Argument::that(static fn (BranchName $b): bool => $b->toString() === $expectedBranch),
            Argument::that(static fn (StartingPoint $s): bool => $s->toString() === $startingPoint),
            $autostashOff,
        )->shouldBeCalledOnce();

        $moveKanbanTicket = $this->prophesize(MoveKanbanTicket::class);
        $moveKanbanTicket->move(Argument::cetera())->shouldBeCalledOnce();

        // System under test
        $workStartHandler = new WorkStartHandler(
            $getKanbanTicket->reveal(),
            new Replace(),
            $switchToNewBranch->reveal(),
            $moveKanbanTicket->reveal(),
        );
        $workStartHandler->handle(new WorkStart(
            $branchNameTemplate,
            $startingPoint,
            autostash: $autostashOff,
            ticketUrl: $ticketUrl,
        ));
    }

    #[TestDox('It can also: move the ticket to In Progress')]
    public function test_it_can_also_move_the_ticket_to_in_progress(): void
    {
        // Fixtures
        $newBranch = BranchNameFixture::makeString();
        $startingPoint = StartingPointFixture::makeString();
        $autostashOff = false;
        $ticketUrl = TicketUrlFixture::makeString();

        // Test doubles
        $getKanbanTicket = $this->prophesize(GetKanbanTicket::class);
        $getKanbanTicket->get(
            Argument::that(static fn (TicketUrl $u): bool => $u->toString() === $ticketUrl),
        )->willReturn(TicketFixture::make());

        $switchToNewBranch = $this->prophesize(SwitchToNewBranch::class);
        $switchToNewBranch->switch(Argument::cetera())->shouldBeCalledOnce();

        $moveKanbanTicket = $this->prophesize(MoveKanbanTicket::class);
        $moveKanbanTicket->move(
            Argument::that(static fn (TicketUrl $u): bool => $u->toString() === $ticketUrl),
            'In Progress',
        )->shouldBeCalledOnce();

        // System under test
        $workStartHandler = new WorkStartHandler(
            $getKanbanTicket->reveal(),
            new Replace(),
            $switchToNewBranch->reveal(),
            $moveKanbanTicket->reveal(),
        );
        $workStartHandler->handle(new WorkStart(
            $newBranch,
            $startingPoint,
            autostash: $autostashOff,
            ticketUrl: $ticketUrl,
        ));
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

        $getKanbanTicket = $this->prophesize(GetKanbanTicket::class);
        $getKanbanTicket->get(Argument::cetera())->shouldNotBeCalled();

        $moveKanbanTicket = $this->prophesize(MoveKanbanTicket::class);
        $moveKanbanTicket->move(Argument::cetera())->shouldNotBeCalled();

        // System under test
        $workStartHandler = new WorkStartHandler(
            $getKanbanTicket->reveal(),
            new Replace(),
            $switchToNewBranch->reveal(),
            $moveKanbanTicket->reveal(),
        );

        $this->expectException(ServerErrorException::class);
        $workStartHandler->handle(new WorkStart(
            $newBranch,
            $startingPoint,
            autostash: $autostashOff,
        ));
    }

    #[TestDox('It fails when: the ticket is not found')]
    public function test_it_fails_when_the_ticket_is_not_found(): void
    {
        // Fixtures
        $newBranch = BranchNameFixture::makeString();
        $startingPoint = StartingPointFixture::makeString();
        $autostashOff = false;
        $ticketUrl = TicketUrlFixture::makeString();

        // Test doubles
        $getKanbanTicket = $this->prophesize(GetKanbanTicket::class);
        $getKanbanTicket->get(Argument::cetera())->willThrow(ValidationFailedException::class);

        $switchToNewBranch = $this->prophesize(SwitchToNewBranch::class);
        $switchToNewBranch->switch(Argument::cetera())->shouldNotBeCalled();

        $moveKanbanTicket = $this->prophesize(MoveKanbanTicket::class);
        $moveKanbanTicket->move(Argument::cetera())->shouldNotBeCalled();

        // System under test
        $workStartHandler = new WorkStartHandler(
            $getKanbanTicket->reveal(),
            new Replace(),
            $switchToNewBranch->reveal(),
            $moveKanbanTicket->reveal(),
        );

        $this->expectException(ValidationFailedException::class);
        $workStartHandler->handle(new WorkStart(
            $newBranch,
            $startingPoint,
            autostash: $autostashOff,
            ticketUrl: $ticketUrl,
        ));
    }
}
