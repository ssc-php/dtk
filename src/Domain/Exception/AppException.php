<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Exception;

class AppException extends \DomainException
{
    protected const int CODE = 500;

    final public function __construct(
        string $message,
        int $code,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function make(string $message, ?\Throwable $previous = null): static
    {
        return new static($message, static::CODE, $previous);
    }
}
