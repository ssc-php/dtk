<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Git;

/**
 * No validation is performed (e.g. invalid name containing space, or existence checking, etc):
 * git will reject invalid branch names when commands are run.
 *
 * @object-type ValueObject
 */
final readonly class BranchName
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
}
