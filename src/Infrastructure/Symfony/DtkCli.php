<?php

declare(strict_types=1);

namespace Ssc\Dtk\Infrastructure\Symfony;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A Symfony CLI Application, with DTK custom help screen (when no commands provided).
 * Displays logo, slogan and list of dtk commands.
 */
final class DtkCli extends Application
{
    /**
     * width: 12
     * height: 5.
     *
     * @var array<string>
     */
    private const array LOGO = [
        '   ██████   ',
        ' ██  ██████ ',
        '████████  ██',
        ' ████  ████ ',
        '   ██████   ',
    ];

    /** @var array<string> */
    private const array SLOGAN = [
        'DTK: Devonshire Tea caKe',
        'Kanban, Git and Deployment,',
        'in one coherent flow.',
    ];

    public function getHelp(): string
    {
        // Colour LOGO in yellow
        $lines = array_map(
            static fn (string $line): string => "<fg=yellow>{$line}</>",
            self::LOGO,
        );

        // Add SLOGAN to the right of LOGO
        $sloganMarginTop = 1;
        $sloganMarginLeft = '  ';
        foreach (self::SLOGAN as $i => $sloganLine) {
            // First line in BOLD
            $styleOpen = 0 === $i ? '<options=bold>' : '';
            $styleClose = 0 === $i ? '</>' : '';

            $lines[$sloganMarginTop + $i] .= "{$sloganMarginLeft}{$styleOpen}{$sloganLine}{$styleClose}";
        }

        return implode("\n", $lines);
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        // Command provided, or a built-in flag like --version/-V: delegate to Symfony
        if (
            null !== $this->getCommandName($input)
            || $input->hasParameterOption(['--version', '-V'], true)
        ) {
            return parent::doRun($input, $output);
        }

        // No command provided, display help (logo, slogan, dtk commands)
        $output->writeln($this->getHelp());
        $output->writeln('<fg=yellow>Available commands:</>');
        foreach ($this->all() as $command) {
            // Skip Symfony default commands (help, list, _complete, completion)
            // which are added by Application::__construct() and cannot be filtered in dtk
            if (!str_starts_with($command::class, 'Ssc\\Dtk\\')) {
                continue;
            }

            $padded = str_pad($command->getName() ?? '', 35);
            $output->writeln("  <info>{$padded}</info> {$command->getDescription()}");
        }

        return Command::SUCCESS;
    }
}
