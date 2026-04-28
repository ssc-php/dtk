<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Integration\Domain\Git\Command\Process;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\Command\Process\ProcessStashLocalChanges;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Mktemp;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Rmdir;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\GitWriteUncommittedChange;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\InitGitRepo;
use Symfony\Component\Process\Process;

#[CoversClass(ProcessStashLocalChanges::class)]
#[Small]
final class ProcessStashLocalChangesTest extends TestCase
{
    #[DataProvider('stashProvider')]
    #[TestDox('It stashes local changes when: $scenario')]
    public function test_it_stashes_local_changes_when(
        string $scenario,
        bool $hasLocalChanges,
    ): void {
        $repoDir = Mktemp::run();

        try {
            InitGitRepo::run($repoDir);
            if ($hasLocalChanges) {
                GitWriteUncommittedChange::run($repoDir);
            }

            $processStashLocalChanges = new ProcessStashLocalChanges();
            $this->assertSame($hasLocalChanges, $processStashLocalChanges->stash());

            if ($hasLocalChanges) {
                $process = new Process(['git', 'diff', '--stat']);
                $process->mustRun();
                $this->assertSame('', trim($process->getOutput()));
            }
        } finally {
            Rmdir::run($repoDir);
        }
    }

    /**
     * @return \Iterator<array{scenario: string, hasLocalChanges: bool}>
     */
    public static function stashProvider(): \Iterator
    {
        yield [
            'scenario' => 'there are local changes (returns `true`)',
            'hasLocalChanges' => true,
        ];
        yield [
            'scenario' => 'there are no local changes (returns `false`)',
            'hasLocalChanges' => false,
        ];
    }

    #[TestDox('It fails when: not inside a git repository')]
    public function test_it_fails_when_not_inside_a_git_repository(): void
    {
        $tmpDir = Mktemp::run();

        try {
            chdir($tmpDir);

            $processStashLocalChanges = new ProcessStashLocalChanges();

            $this->expectException(ServerErrorException::class);
            $processStashLocalChanges->stash();
        } finally {
            Rmdir::run($tmpDir);
        }
    }
}
