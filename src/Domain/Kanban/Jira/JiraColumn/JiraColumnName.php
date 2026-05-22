<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Kanban\Jira\JiraColumn;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;

/**
 * @object-type ValueObject
 */
final readonly class JiraColumnName
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
     * @throws ValidationFailedException If $value is empty
     */
    public static function fromString(string $value): self
    {
        if ('' === $value) {
            throw ValidationFailedException::make(
                'Invalid "JiraColumnName" parameter: it cannot be empty',
            );
        }

        return new self($value);
    }
}
