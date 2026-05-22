<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Ticket;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;

/**
 * @object-type ValueObject
 */
final readonly class TicketId
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
     * @throws ValidationFailedException If $value isn't valid
     */
    public static function fromString(string $value): self
    {
        if ('' === $value) {
            throw ValidationFailedException::make(
                'Invalid "TicketId" parameter: it cannot be empty',
            );
        }

        return new self($value);
    }
}
