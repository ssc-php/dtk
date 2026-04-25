<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Git\Command\Process;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\BranchName;
use Ssc\Dtk\Domain\Git\Command\RestoreStashedChanges;
use Ssc\Dtk\Domain\Git\Command\StashLocalChanges;
use Ssc\Dtk\Domain\Git\Command\SwitchToNewBranch;
use Ssc\Dtk\Domain\Git\StartingPoint;
use Symfony\Component\Process\Process;

/**
 * Uses git switch:
 *
 * ```
 * git switch -c <new-branch> <starting-point>
 * ```
 *
 * This will fail if there are uncommited changes. Which is why we added `autostash` support:
 *
 * ```
 * git stash
 * git switch -c <new-branch> <starting-point>
 * git stash pop
 * ```
 */
final readonly class ProcessSwitchToNewBranch implements SwitchToNewBranch
{
    public function __construct(
        private StashLocalChanges $stashLocalChanges,
        private RestoreStashedChanges $restoreStashedChanges,
    ) {
    }

    /**
     * @throws ServerErrorException If switching to the new branch fails
     */
    public function switch(
        BranchName $newBranch,
        StartingPoint $startingPoint,
        bool $autostash = false,
    ): void {
        $stashed = $autostash && $this->stashLocalChanges->stash();

        $process = new Process([
            'git',
            'switch',
            '-c', $newBranch->toString(),
            $startingPoint->toString(),
        ]);
        $process->run();

        if (0 !== $process->getExitCode()) {
            if ($stashed) {
                $this->restoreStashedChanges->restore();
            }

            throw ServerErrorException::make($process->getErrorOutput());
        }

        if ($stashed) {
            $this->restoreStashedChanges->restore();
        }
    }
}
