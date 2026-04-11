<?php

declare(strict_types=1);

namespace Ssc\Dtk\Infrastructure\Symfony;

use Ssc\Dtk\UserInterface\Cli\TokensSaveCommand;
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
     * Width x Height: 12 x 5.
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

    /** @var array<string, class-string> */
    public const array COMMANDS = [
        TokensSaveCommand::NAME => TokensSaveCommand::class,
    ];

    /** @var array<string> */
    private const array SLOGAN = [
        'DTK: Devonshire Tea caKe',
        'Kanban, Git and Deployment,',
        'in one coherent flow.',
    ];

    /**
     * Output:
     *
     *    ██████
     *  ██  ██████   DTK: Devonshire Tea caKe
     * ████████  ██  Kanban, Git and Deployment,
     *  ████  ████   in one coherent flow.
     *    ██████
     *
     *   [INFO] Omit any option or env var to be prompted for it interactively.
     *
     * Available commands:
     *   # Save token to allow DTK to access a service (Github, YouTrack, etc)
     *   DTK_TOKEN=… tokens:save --service=…
     *   ...
     */
    #[\Override]
    public function getHelp(): string
    {
        // LOGO, in yellow
        $lines = array_map(
            static fn (string $line): string => "<fg=yellow>{$line}</>",
            self::LOGO,
        );

        // SLOGAN floating right of logo (first line in bold)
        $sloganMarginTop = 1;
        $sloganMarginLeft = '  ';
        foreach (self::SLOGAN as $i => $sloganLine) {
            $styleOpen = 0 === $i ? '<options=bold>' : '';
            $styleClose = 0 === $i ? '</>' : '';
            $lines[$sloganMarginTop + $i] .= "{$sloganMarginLeft}{$styleOpen}{$sloganLine}{$styleClose}";
        }

        // INFO about interractive questions
        $lines[] = '';
        $lines[] = '<fg=blue>  [INFO] Omit any option or env var to be prompted for it interactively.</>';
        $lines[] = '';

        // List of DTK commands
        $lines[] = '<fg=yellow>Available commands:</>';

        $globalOptionNames = array_keys($this->getDefinition()->getOptions());
        foreach (self::COMMANDS as $name => $class) {
            $command = $this->get($name);

            // SHORT_DESCRIPTION: first line of DESCRIPTION, in a gray comment
            $shortDescription = array_first(explode("\n", $command->getDescription()));
            $lines[] = "  <fg=gray># {$shortDescription}</>";

            // ENV VARS (name in cyan, value in gray)
            $envVars = '';
            foreach (array_keys(\defined("{$class}::ENV_VARS") ? $class::ENV_VARS : []) as $envVar) {
                $envVars .= "<fg=cyan>{$envVar}</><fg=gray>=…</> ";
            }

            // Command name (in green)
            $commandName = "<fg=green>{$name}</>";

            // Options (name in cyan, value in gray)
            $options = '';
            foreach ($command->getDefinition()->getOptions() as $option) {
                if (\in_array($option->getName(), $globalOptionNames, true)) {
                    continue;
                }

                $options .= " <fg=cyan>--{$option->getName()}</><fg=gray>=…</>";
            }

            $lines[] = "  {$envVars}{$commandName}{$options}";
        }

        return implode("\n", $lines);
    }

    #[\Override]
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        if (
            null !== $this->getCommandName($input)
            || $input->hasParameterOption(['--version', '-V'], true)
        ) {
            return parent::doRun($input, $output);
        }

        $output->writeln($this->getHelp());

        return Command::SUCCESS;
    }
}
