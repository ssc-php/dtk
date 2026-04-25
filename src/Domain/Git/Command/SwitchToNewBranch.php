<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Git\Command;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\BranchName;
use Ssc\Dtk\Domain\Git\StartingPoint;

/**
 * @object-type Service
 */
interface SwitchToNewBranch
{
    /**
     * @throws ServerErrorException If the new branch name is invalid (e.g. contains a space)
     * @throws ServerErrorException If the starting point name is invalid (e.g. contains a space)
     * @throws ServerErrorException If the starting point does not exist
     * @throws ServerErrorException If the new branch already exists
     * @throws ServerErrorException If uncommitted changes conflict with the starting point and autostash is off
     * @throws ServerErrorException If uncommitted changes conflict with the starting point and autostash is on (stash pop merge conflict)
     */
    public function switch(
        BranchName $newBranch,
        StartingPoint $startingPoint,
        bool $autostash = false,
    ): void;
}
