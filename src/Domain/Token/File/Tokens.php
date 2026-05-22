<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\File;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;

final readonly class Tokens
{
    /** @param array<string, Token> $tokens */
    private function __construct(
        private array $tokens,
    ) {
    }

    /**
     * @param array<array-key, mixed> $rawTokens
     *
     * @throws ServerErrorException      If any token value is not a string
     * @throws ValidationFailedException If any service name is not a known service
     * @throws ValidationFailedException If any token value is empty
     */
    public static function fromArray(array $rawTokens): self
    {
        $tokens = [];
        foreach ($rawTokens as $rawService => $rawToken) {
            $service = Service::fromString((string) $rawService);

            if (!\is_string($rawToken)) {
                throw ServerErrorException::make(
                    "Invalid \"token\" parameter: should be a string (key: `{$rawService}`)",
                );
            }

            $tokens[$service->value] = Token::fromString($rawToken);
        }

        return new self($tokens);
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return array_map(static fn (Token $token): string => $token->toString(), $this->tokens);
    }
}
