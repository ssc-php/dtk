<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Ticket;

use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @object-type ValueObject
 */
final readonly class Slug
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
        return new self(
            new AsciiSlugger()
                ->slug($value)
                ->lower()
                ->toString(),
        );
    }
}
