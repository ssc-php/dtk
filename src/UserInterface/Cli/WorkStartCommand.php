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

    /** @var list<string> */
    public const array REQUIRED_OPTIONS = ['new-branch'];

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
        #[Option(description: 'Prompt for missing options interactively')]
        bool $interactive = false,
    ): int {
        $newBranch = $this->askNewBranch($io, $newBranch, $interactive);
        if ('' === $newBranch) {
            $io->error('Missing required option: --new-branch');

            return Command::INVALID;
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

    private function askNewBranch(SymfonyStyle $io, string $newBranch, bool $interactive): string
    {
        if ('' !== $newBranch || !$interactive) {
            return $newBranch;
        }

        $entered = $io->ask('new-branch');

        return \is_string($entered) ? $entered : '';
    }
}
