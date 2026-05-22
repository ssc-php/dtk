<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Ticket;

/**
 * @object-type ValueObject
 */
final readonly class Title
{
    private function __construct(
        private string $value,
    ) {
    }

    public function toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toSlug(): Slug
    {
        return Slug::fromString($this->value);
    }
}
