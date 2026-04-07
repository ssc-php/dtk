<?php

declare(strict_types=1);

namespace Ssc\Dtk\Application\TokensSave;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\SaveToken;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;

/**
 * @object-type UseCase
 */
final readonly class TokensSaveHandler
{
    public function __construct(
        private SaveToken $saveToken,
    ) {
    }

    /**
     * @throws ValidationFailedException If service is invalid
     * @throws ValidationFailedException If token is invalid
     */
    public function handle(TokensSave $tokensSave): void
    {
        $this->saveToken->save(
            Service::fromString($tokensSave->service),
            Token::fromString($tokensSave->token),
        );
    }
}
