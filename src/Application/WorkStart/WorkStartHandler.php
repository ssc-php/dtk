<?php

declare(strict_types=1);

namespace Ssc\Dtk\Application\WorkStart;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Git\BranchName;
use Ssc\Dtk\Domain\Git\Command\SwitchToNewBranch;
use Ssc\Dtk\Domain\Git\StartingPoint;
use Ssc\Dtk\Domain\Template\Replace;

/**
 * @object-type UseCase
 */
final readonly class WorkStartHandler
{
    public function __construct(
        private Replace $replace,
        private SwitchToNewBranch $switchToNewBranch,
    ) {
    }

    /**
     * @throws ServerErrorException If a git operation fails unexpectedly
     */
    public function handle(WorkStart $workStart): void
    {
        $newBranch = $this->replace->in($workStart->newBranch, [
            'ticket_id' => $workStart->ticketId,
        ]);

        $this->switchToNewBranch->switch(
            BranchName::fromString($newBranch),
            StartingPoint::fromString($workStart->startingPoint),
            $workStart->autostash,
        );
    }
}
