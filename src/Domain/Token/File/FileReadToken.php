<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\File;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\Composing\ReadTokenStrategy;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;

final readonly class FileReadToken implements ReadTokenStrategy
{
    public function __construct(
        private FileReadTokens $fileReadTokens,
    ) {
    }

    public static function priority(): int
    {
        return 0;
    }

    public function supports(): bool
    {
        return true;
    }

    /**
     * @throws ValidationFailedException If no token stored for the service
     * @throws ServerErrorException      If FileReadTokens::get() fails
     */
    public function read(Service $service): Token
    {
        $tokens = $this->fileReadTokens->read()->toArray();
        if (!\array_key_exists($service->value, $tokens)) {
            throw ValidationFailedException::make(
                "Missing token for \"{$service->value}\": save one first with `dtk tokens:save --service {$service->value} --interractive`",
            );
        }

        return Token::fromString($tokens[$service->value]);
    }
}
