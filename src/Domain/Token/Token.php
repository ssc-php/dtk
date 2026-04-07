<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;

/**
 * @object-type ValueObject
 */
final readonly class Token
{
    private function __construct(
        #[\SensitiveParameter]
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
    public static function fromString(
        #[\SensitiveParameter]
        string $value,
    ): self {
        if ('' === $value) {
            throw ValidationFailedException::make(
                'Invalid "Token" parameter: it cannot be empty',
            );
        }

        return new self($value);
    }
}
