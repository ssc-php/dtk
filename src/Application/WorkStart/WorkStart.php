<?php

declare(strict_types=1);

namespace Ssc\Dtk\Application\WorkStart;

/**
 * @object-type DataTransferObject
 */
final readonly class WorkStart
{
    public function __construct(
        public string $newBranch = '',
        public string $startingPoint = 'origin/main',
        public string $ticketId = '',
        public bool $autostash = false,
        public string $ticketUrl = '',
    ) {
    }
}
