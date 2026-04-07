<?php

declare(strict_types=1);

namespace Ssc\Dtk\Infrastructure\Symfony;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Helper\Helper;

/**
 * A Symfony TextDescriptor, with DTK custom command help screen.
 * Displays description, usage with env vars, environment variables and options.
 * Global options are hidden by default, shown with -v.
 */
final class DtkCommandDescriptor extends TextDescriptor
{
    /**
     * Output:
     *
     * ```
     * Description:
     *   Save token to allow DTK to access a service (Github, YouTrack, etc).
     *
     *   These are stored in the OS keyring (or if not found, in the filesystem).
     *
     *   [INFO] Omit any option or env var to be prompted for it interactively.
     *
     * Usage:
     *   DTK_TOKEN=… tokens:save --service=…
     *
     * Environment Variables:
     *   DTK_TOKEN=…  The service token to store (e.g. for Github: Personal Access Token)
     *
     * Options:
     *       --service=…  Service name (one of: youtrack)
     *   -h, --help       Display help for the given command.
     *   ...              (global options shown with -v)
     * ```
     *
     * When there are more than 3 env vars or options, they are split into groups of 3, one per line:
     *
     * ```
     * Usage:
     *   DTK_TOKEN=… tokens:save --service=… --help --silent \
     *     --quiet --verbose --version \
     *     --ansi
     * ```
     *
     * @param array<string, mixed> $options
     */
    #[\Override]
    protected function describeCommand(Command $command, array $options = []): void
    {
        if ($command instanceof LazyCommand) {
            $command = $command->getCommand();
        }

        // Populates $command->getDefinition() with global options (needed for verbose mode)
        $command->mergeApplicationDefinition(false);

        $lines = [];

        // Description
        $lines[] = '<fg=magenta>Description:</>';
        foreach (explode("\n", $command->getDescription()) as $descriptionLine) {
            $lines[] = "  {$descriptionLine}";
        }

        $lines[] = '';

        // INFO about interractive questions
        $lines[] = '  <fg=blue>[INFO] Omit any option or env var to be prompted for it interactively.</>';
        $lines[] = '';

        // Usage: `ENVVAR=… command --option=…`
        $lines[] = '<fg=magenta>Usage:</>';

        // ENV VARS
        $commandClass = $command::class;
        /** @var array<string, string> $envVars */
        $envVars = \defined("{$commandClass}::ENV_VARS") ? $commandClass::ENV_VARS : [];
        $envVarNames = array_keys($envVars);
        $envVarTokens = [];
        foreach ($envVarNames as $envVarName) {
            $envVarTokens[] = "<fg=cyan>{$envVarName}</><fg=gray>=…</>";
        }

        // Up to 3 per line:
        //   0:   <command>
        //   1-3: DTK_TOKEN=… <command>
        //   4+:  DTK_TOKEN=… FOO=… BAR=… \
        //          BAZ=… <command>
        $envVarCount = \count($envVarTokens);
        $envVarPart = match (true) {
            0 === $envVarCount => '',
            $envVarCount <= 3 => implode(' ', $envVarTokens).' ',
            default => implode(" \\\n  ", array_map(
                static fn ($c): string => implode(' ', $c),
                array_chunk($envVarTokens, 3),
            ))." \\\n  ",
        };

        // Options (name in cyan, value in gray)
        $options = $this->output->isVerbose()
            ? $command->getDefinition()->getOptions() // Global options (--help, --debug, etc), only shown in verbose move (-v)
            : $command->getNativeDefinition()->getOptions(); // Just the command's options
        $optionTokens = [];
        foreach ($options as $optionName => $inputOption) {
            $shortcut = $inputOption->getShortcut() ?: '';
            $shortcutPrefix = '' !== $shortcut
                ? "<fg=cyan>-{$shortcut}|</>"
                : '';
            $valueSuffix = $inputOption->acceptValue()
                ? '<fg=gray>=…</>'
                : '';
            $optionTokens[] = "{$shortcutPrefix}<fg=cyan>--{$optionName}</>{$valueSuffix}";
        }

        // Up to 3 per line:
        //   0:   <command>
        //   1-3: <command> --service=… --help
        //   4+:  <command> --service=… --help --debug \
        //          --quiet --verbose --version
        $optionCount = \count($optionTokens);
        $optionPart = match (true) {
            0 === $optionCount => '',
            $optionCount <= 3 => ' '.implode(' ', $optionTokens),
            default => ' '.implode(" \\\n    ", array_map(
                static fn ($c): string => implode(' ', $c),
                array_chunk($optionTokens, 3),
            )),
        };

        $lines[] = "  {$envVarPart}<fg=green>{$command->getName()}</>{$optionPart}";

        // Environment variables (name in cyan)
        if ([] !== $envVars) {
            $maxWidth = 0;
            foreach ($envVarNames as $envVarName) {
                $maxWidth = max($maxWidth, Helper::width("{$envVarName}=…"));
            }

            $lines[] = '';
            $lines[] = '<fg=bright-magenta>Environment Variables:</>';
            foreach ($envVars as $name => $envDescription) {
                $padding = str_repeat(' ', $maxWidth - Helper::width("{$name}=…") + 2);
                $lines[] = "  <fg=cyan>{$name}=…</>{$padding}{$envDescription}";
            }
        }

        // Options (name in cyan, shortcut in cyan)
        if ([] !== $options) {
            $maxShortcutWidth = 0;
            $maxOptionWidth = 0;
            foreach ($options as $n => $opt) {
                $sc = $opt->getShortcut() ?: '';
                if ('' !== $sc) {
                    $maxShortcutWidth = max($maxShortcutWidth, Helper::width("-{$sc}, "));
                }

                $maxOptionWidth = max($maxOptionWidth, Helper::width($opt->acceptValue() ? "--{$n}=…" : "--{$n}"));
            }

            $lines[] = '';
            $lines[] = '<fg=magenta>Options:</>';
            foreach ($options as $option => $inputOption) {
                $shortcut = $inputOption->getShortcut() ?: '';
                $shortcutWidth = '' !== $shortcut ? Helper::width("-{$shortcut}, ") : 0;
                $shortcutPart = '' !== $shortcut
                    ? "<fg=cyan>-{$shortcut}</>, ".str_repeat(' ', $maxShortcutWidth - $shortcutWidth)
                    : str_repeat(' ', $maxShortcutWidth);
                $optionDisplay = $inputOption->acceptValue() ? "--{$option}=…" : "--{$option}";
                $padding = str_repeat(' ', $maxOptionWidth - Helper::width($optionDisplay) + 2);
                $valueSuffix = $inputOption->acceptValue() ? '<fg=gray>=…</>' : '';
                $lines[] = "  {$shortcutPart}<fg=cyan>--{$option}</>{$valueSuffix}{$padding}{$inputOption->getDescription()}";
            }
        }

        $lines[] = '';

        $this->write(implode("\n", $lines), true);
    }
}
