<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Integration\Domain\Git\Command\Process;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\BranchName;
use Ssc\Dtk\Domain\Git\Command\Process\ProcessRestoreStashedChanges;
use Ssc\Dtk\Domain\Git\Command\Process\ProcessStashLocalChanges;
use Ssc\Dtk\Domain\Git\Command\Process\ProcessSwitchToNewBranch;
use Ssc\Dtk\Domain\Git\StartingPoint;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Mktemp;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Rmdir;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\GitBranch;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\GitBranchWithConflictingCommit;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\GitWriteUncommittedChange;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\InitGitRepo;
use Symfony\Component\Process\Process;

#[CoversClass(ProcessSwitchToNewBranch::class)]
#[Small]
final class ProcessSwitchToNewBranchTest extends TestCase
{
    #[TestDox('It switches to a new branch from a starting point')]
    public function test_it_switches_to_new_branch_from_starting_point(): void
    {
        $repoDir = Mktemp::run();

        try {
            InitGitRepo::run($repoDir);

            $processSwitchToNewBranch = new ProcessSwitchToNewBranch(
                new ProcessStashLocalChanges(),
                new ProcessRestoreStashedChanges(),
            );
            $processSwitchToNewBranch->switch(
                BranchName::fromString('feat/cunning-plan'),
                StartingPoint::fromString('main'),
            );

            $process = new Process(['git', 'branch', '--show-current']);
            $process->mustRun();
            $this->assertSame('feat/cunning-plan', trim($process->getOutput()));
        } finally {
            Rmdir::run($repoDir);
        }
    }

    #[DataProvider('autostashProvider')]
    #[TestDox('It restores stashed changes when: $scenario and autostash is on')]
    public function test_it_restores_stashed_changes_when_autostash_is_on(
        string $scenario,
        string $startingPoint,
    ): void {
        $repoDir = Mktemp::run();
        try {
            InitGitRepo::run($repoDir);
            GitWriteUncommittedChange::run($repoDir);

            $processSwitchToNewBranch = new ProcessSwitchToNewBranch(
                new ProcessStashLocalChanges(),
                new ProcessRestoreStashedChanges(),
            );

            try {
                $processSwitchToNewBranch->switch(
                    BranchName::fromString('feat/cunning-plan'),
                    StartingPoint::fromString($startingPoint),
                    true,
                );
            } catch (ServerErrorException) {
            }

            $this->assertSame('A bigger turnip.', file_get_contents("{$repoDir}/turnip.txt"));
        } finally {
            Rmdir::run($repoDir);
        }
    }

    /**
     * @return \Iterator<array{scenario: string, startingPoint: string}>
     */
    public static function autostashProvider(): \Iterator
    {
        yield [
            'scenario' => 'switching',
            'startingPoint' => 'main',
        ];
        yield [
            'scenario' => 'switching failed',
            'startingPoint' => 'no-such-branch',
        ];
    }

    #[DataProvider('failureProvider')]
    #[TestDox('It fails when: $scenario')]
    public function test_it_fails_when(
        string $scenario,
        string $setup,
        string $newBranch,
        string $startingPoint,
        bool $autostash,
    ): void {
        $repoDir = Mktemp::run();

        try {
            InitGitRepo::run($repoDir);
            match ($setup) {
                'existing-branch' => GitBranch::run($repoDir, $newBranch),
                'conflicting-branch' => GitBranchWithConflictingCommit::run($repoDir, $startingPoint),
                default => null,
            };

            $processSwitchToNewBranch = new ProcessSwitchToNewBranch(
                new ProcessStashLocalChanges(),
                new ProcessRestoreStashedChanges(),
            );

            $this->expectException(ServerErrorException::class);
            $processSwitchToNewBranch->switch(
                BranchName::fromString($newBranch),
                StartingPoint::fromString($startingPoint),
                $autostash,
            );
        } finally {
            Rmdir::run($repoDir);
        }
    }

    /**
     * @return \Iterator<array{scenario: string, setup: string, newBranch: string, startingPoint: string, autostash: bool}>
     */
    public static function failureProvider(): \Iterator
    {
        yield [
            'scenario' => 'the new branch name is invalid (e.g. contains spaces)',
            'setup' => 'default',
            'newBranch' => 'invalid branch name',
            'startingPoint' => 'main',
            'autostash' => false,
        ];
        yield [
            'scenario' => 'the new branch already exists',
            'setup' => 'existing-branch',
            'newBranch' => 'feat/cunning-plan',
            'startingPoint' => 'main',
            'autostash' => false,
        ];
        yield [
            'scenario' => 'the starting point name is invalid (e.g. contains spaces)',
            'setup' => 'default',
            'newBranch' => 'feat/cunning-plan',
            'startingPoint' => 'invalid starting point',
            'autostash' => false,
        ];
        yield [
            'scenario' => 'the starting point does not exist',
            'setup' => 'default',
            'newBranch' => 'feat/cunning-plan',
            'startingPoint' => 'no-such-branch',
            'autostash' => false,
        ];
        yield [
            'scenario' => 'uncommitted changes conflict with the starting point and autostash is off (git switch fail)',
            'setup' => 'conflicting-branch',
            'newBranch' => 'feat/cunning-plan',
            'startingPoint' => 'other',
            'autostash' => false,
        ];
        yield [
            'scenario' => 'uncommitted changes conflict with the starting point and autostash is on (git stash pop fail)',
            'setup' => 'conflicting-branch',
            'newBranch' => 'feat/cunning-plan',
            'startingPoint' => 'other',
            'autostash' => true,
        ];
    }
}
