<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Git\Command\Process;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\Command\StashLocalChanges;
use Symfony\Component\Process\Process;

/**
 * Uses git stash:
 *
 * ```
 * git stash
 * ```
 */
final class ProcessStashLocalChanges implements StashLocalChanges
{
    /**
     * @throws ServerErrorException If git stash fails
     */
    public function stash(): bool
    {
        $process = new Process(['git', 'stash']);
        $process->run();

        if (0 !== $process->getExitCode()) {
            throw ServerErrorException::make($process->getErrorOutput());
        }

        return !str_contains($process->getOutput(), 'No local changes to save');
    }
}
