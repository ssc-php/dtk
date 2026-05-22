<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;

/**
 * @object-type ValueObject
 */
final readonly class BaseUrl
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
     * @throws ValidationFailedException If $value isn't a valid base URL
     */
    public static function fromString(string $value): self
    {
        if (1 !== preg_match('#^https?://[^/]+$#', $value)) {
            throw ValidationFailedException::make(
                "Invalid \"BaseUrl\" parameter: it should be a valid base URL (`https://<dns>`) (`{$value}` given)",
            );
        }

        return new self($value);
    }
}
