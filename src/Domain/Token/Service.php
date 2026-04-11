<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;

enum Service: string
{
    case Youtrack = 'youtrack';

    /**
     * @throws ValidationFailedException If $value isn't a valid service name
     */
    public static function fromString(string $value): self
    {
        $validNames = implode('`, `', self::toArray());

        return self::tryFrom($value) ?? throw ValidationFailedException::make(
            "Invalid \"Service\" parameter: it should be a valid service name (`{$validNames}`) (`{$value}` given)",
        );
    }

    public function toString(): string
    {
        return $this->value;
    }

    /** @return list<string> */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toListString(): string
    {
        return implode(', ', self::toArray());
    }
}
