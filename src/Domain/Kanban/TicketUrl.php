<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;

/**
 * @object-type ValueObject
 */
final readonly class TicketUrl
{
    private function __construct(
        private string $value,
    ) {
    }

    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @throws ValidationFailedException If $value isn't a valid ticket URL
     */
    public static function fromString(string $value): self
    {
        if (1 !== preg_match('#^https?://[^/]+/.+$#', $value)) {
            throw ValidationFailedException::make(
                "Invalid \"TicketUrl\" parameter: it should be a valid ticket URL (`https://<dns>/<path>`) (`{$value}` given)",
            );
        }

        return new self($value);
    }
}
