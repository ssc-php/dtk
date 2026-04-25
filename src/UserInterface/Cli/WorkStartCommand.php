<?php

declare(strict_types=1);

namespace Ssc\Dtk\UserInterface\Cli;

use Ssc\Dtk\Application\WorkStart\WorkStart;
use Ssc\Dtk\Application\WorkStart\WorkStartHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: WorkStartCommand::NAME,
    description: WorkStartCommand::DESCRIPTION,
)]
final readonly class WorkStartCommand
{
    public const string NAME = 'work:start';

    public const string DESCRIPTION = <<<'TXT'
    Create a new git branch.
    TXT;

    public function __construct(
        private WorkStartHandler $workStartHandler,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'Name for the new branch (e.g. `PRJ-4423/feat/cunning-plan`)')]
        string $newBranch = '',
        #[Option(description: 'Branch to use as the starting point for the new one (e.g. `origin/master`, default: `origin/main`)')]
        string $startingPoint = 'origin/main',
        #[Option(description: 'Ticket identifier (e.g. `PRJ-4423`)')]
        string $ticketId = '',
        #[Option(description: 'Stash uncommitted changes before checkout and restore afterwards')]
        bool $autostash = false,
    ): int {
        if ('' === $newBranch) {
            $entered = $io->ask('new-branch');
            $newBranch = \is_string($entered) ? $entered : '';
        }

        $this->workStartHandler->handle(new WorkStart(
            $newBranch,
            $startingPoint,
            $ticketId,
            $autostash,
        ));

        $io->success('Work started');

        return Command::SUCCESS;
    }
}
