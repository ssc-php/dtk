<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Git\Command\Process;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\Command\RestoreStashedChanges;
use Symfony\Component\Process\Process;

/**
 * Uses git stash pop:
 *
 * ```
 * git stash pop
 * ```
 */
final class ProcessRestoreStashedChanges implements RestoreStashedChanges
{
    /**
     * @throws ServerErrorException If git stash pop fails
     */
    public function restore(): void
    {
        $process = new Process(['git', 'stash', 'pop']);
        $process->run();

        if (0 !== $process->getExitCode()) {
            throw ServerErrorException::make($process->getErrorOutput());
        }
    }
}
