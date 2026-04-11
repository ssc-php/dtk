<?php

declare(strict_types=1);

namespace Ssc\Dtk\Infrastructure\Symfony;

use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * Formats log records as styled Symfony console lines, consistent with SymfonyStyle output.
 *
 * ```
 *  [ERROR] message      (red)     always shown
 *  [WARNING] message    (yellow)  always shown
 *  [INFO] message       (blue)    shown with -vv
 * ```
 */
final class DtkConsoleLogFormatter implements FormatterInterface
{
    public function format(LogRecord $record): string
    {
        $message = $record->message;

        return match ($record->level->value) {
            Level::Error->value => "\n <fg=red>{$message}</>\n",
            Level::Warning->value => "\n <fg=yellow>{$message}</>\n",
            Level::Info->value => "\n <fg=blue>{$message}</>\n",
            default => "\n <fg=gray>{$message}</>\n",
        };
    }

    /**
     * @param array<LogRecord> $records
     *
     * @return array<string>
     */
    public function formatBatch(array $records): array
    {
        return array_map($this->format(...), $records);
    }
}
