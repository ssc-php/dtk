<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\Token;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(Token::class)]
#[Small]
final class TokenTest extends TestCase
{
    #[TestDox('It can be converted from/to string')]
    public function test_it_can_be_converted_from_and_to_string(): void
    {
        $stringToken = TokenFixture::makeString();
        $token = Token::fromString($stringToken);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertSame($stringToken, $token->toString());
    }

    #[TestDox('It fails when raw token $scenario')]
    #[DataProvider('invalidTokenProvider')]
    public function test_it_fails_when_raw_token_is_invalid(
        string $scenario,
        string $invalidToken,
    ): void {
        $this->expectException(ValidationFailedException::class);

        Token::fromString($invalidToken);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     invalidToken: string,
     * }>
     */
    public static function invalidTokenProvider(): \Iterator
    {
        yield [
            'scenario' => 'is empty',
            'invalidToken' => '',
        ];
    }
}
