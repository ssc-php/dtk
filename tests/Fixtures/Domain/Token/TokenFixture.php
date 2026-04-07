<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Token;

use Ssc\Dtk\Domain\Token\Token;

final readonly class TokenFixture
{
    public static function make(): Token
    {
        return Token::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'token-'.bin2hex(random_bytes(4));
    }
}
