<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Integration\Domain\Git\Command\Process;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\Command\Process\ProcessRestoreStashedChanges;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Mktemp;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Rmdir;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\GitStash;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\GitWriteUncommittedChange;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\InitGitRepo;

#[CoversClass(ProcessRestoreStashedChanges::class)]
#[Small]
final class ProcessRestoreStashedChangesTest extends TestCase
{
    #[TestDox('It restores stashed changes')]
    public function test_it_restores_stashed_changes(): void
    {
        $repoDir = Mktemp::run();

        try {
            InitGitRepo::run($repoDir);
            GitWriteUncommittedChange::run($repoDir);
            GitStash::run($repoDir);

            $processRestoreStashedChanges = new ProcessRestoreStashedChanges();
            $processRestoreStashedChanges->restore();

            $this->assertSame('A bigger turnip.', file_get_contents("{$repoDir}/turnip.txt"));
        } finally {
            Rmdir::run($repoDir);
        }
    }

    #[DataProvider('failureProvider')]
    #[TestDox('It fails when: $scenario')]
    public function test_it_fails_when(
        string $scenario,
        string $setup,
    ): void {
        $tmpDir = Mktemp::run();
        try {
            match ($setup) {
                'no-stash' => InitGitRepo::run($tmpDir),
                'no-git-repo' => chdir($tmpDir),
                default => null,
            };

            $this->expectException(ServerErrorException::class);
            new ProcessRestoreStashedChanges()->restore();
        } finally {
            Rmdir::run($tmpDir);
        }
    }

    /**
     * @return \Iterator<array{scenario: string, setup: string}>
     */
    public static function failureProvider(): \Iterator
    {
        yield [
            'scenario' => 'there is no stash to restore',
            'setup' => 'no-stash',
        ];
        yield [
            'scenario' => 'not inside a git repository',
            'setup' => 'no-git-repo',
        ];
    }
}
