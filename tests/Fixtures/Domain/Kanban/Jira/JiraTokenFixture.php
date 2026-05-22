<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Fixtures\Domain\Kanban\Jira;

use Ssc\Dtk\Domain\Token\Token;

final readonly class JiraTokenFixture
{
    public static function make(): Token
    {
        return Token::fromString(self::makeString());
    }

    public static function makeString(): string
    {
        return 'user@example.com:api-token-'.bin2hex(random_bytes(4));
    }
}
