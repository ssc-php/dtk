<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Git;

/**
 * The starting point from which a new branch is created: a branch name, tag, commit hash, or rev like "HEAD~2".
 *
 * No validation is performed (e.g. invalid name containing space, or existence checking, etc):
 * git will reject invalid starting points when commands are run.
 *
 * @object-type ValueObject
 */
final readonly class StartingPoint
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
